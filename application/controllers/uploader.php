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

class uploader extends CI_Controller
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
		$folder = 'logs/biometric_logs';
		$log_file = $folder.'/'.date('Y-m-d').'.txt';
		if(!file_exists($folder)) 
			mkdir($folder, 0777, true);

		
		// log message
		$log_msg = date('Ymd H:i:s')." START UPLOADING \r\n";
		write_file($log_file, $log_msg, 'a');

		
		$this->db->where('location_id', 1);
		$this->db->where('deleted', 0);
		$location = $this->db->get('timekeeping_location')->row();

		$db2 = $this->load->database('ms_sql', TRUE);
		$sql = $location->query;

		$qry = $db2->query($sql);

		if( $qry->num_rows > 0 ){
			$msdata = $qry->result();
		}

		$not_exist_biometrics = array();
		$records_read = 0;
		
		// log message
		$log_msg = date('Ymd H:i:s').' '.$qry->num_rows()." record(s) read. \r\n";
		write_file($log_file, $log_msg, 'a');

		$qry->free_result();

		$biometric_array = array();
		$insert_array = array();


		foreach ($msdata as $row) {
			$row->processed = 0;

			if (!array_key_exists($row->employee_id, $biometric_array)) {
				$this->db->where('biometric_id', $row->employee_id);
				$this->db->where('resigned', 0);
				$this->db->where('deleted', 0);
		
				$employee = $this->db->get('employee');

				if ($employee->num_rows() > 0) {
					$biometric_array[$row->employee_id] = $employee->row()->employee_id;
				} else {
					$biometric_array[$row->employee_id] = '';

					//if(!in_array($row->employee_id, $not_exist_biometrics))
					//	$not_exist_biometrics[] = $row->employee_id;

					// log message
					$log_msg = date('Ymd H:i:s').' '.$row->employee_id." does not exist. \r\n";
					write_file($log_file, $log_msg, 'a');

				}
			}

			$row->employee_id = $biometric_array[$row->employee_id];			

			//check if data exists
			$where = array(
				'employee_id' => $row->employee_id,
				'checktime' => $row->checktime,
				'checktype' => $row->checktype
			);
			$data = $this->db->get_where('employee_dtr_raw', $where);
			if($data->num_rows() == 0) $insert_array[] = (array) $row;
		}


		if(  sizeof($insert_array) > 0) $this->db->insert_batch('employee_dtr_raw', $insert_array);

		if ($this->db->_error_message() == '') {
			print ('Running...');

			// log message
			$log_msg = date('Ymd H:i:s')." Start running process time. \r\n";
			write_file($log_file, $log_msg, 'a');

			if ($this->config->item('client_no') == 1){
				process_time_raw(1, 0);
			}
			else if ($this->config->item('client_no') == 2){
				print ('Executing process_time_raw...');
				process_time_raw_oams(1, 0);
			}				

			// log message
			$log_msg = date('Ymd H:i:s')." End process time. \r\n";
			write_file($log_file, $log_msg, 'a');
			
		}

		else {
			print ('ERROR: ' . $this->db->_error_message() . "\n");			
		}

		// log message
		$log_msg = date('Ymd H:i:s')." END UPLOADING \r\n";
		write_file($log_file, $log_msg, 'a');	
	}

	function fb_payroll_setup(){
		$this->load->library('encrypt');
		switch(CLIENT_DIR){
			case 'balfour':
				$this->_fb_empsetup_daily_payroll();
				$this->_fb_empsetup_employee('uploads/firstbalfour/empsetup_daily_employee.txt', 'Daily Rates');
				$this->_fb_empsetup_monthly_payroll();
				$this->_fb_empsetup_employee('uploads/firstbalfour/empsetup_monthly_employee.txt', 'Monthly Rates');
				$this->_fb_employee_loans();
				$this->_fb_employee_YTD('uploads/firstbalfour/employee_YTD_wtax.txt', 'Withholding Tax');	
				$this->_fb_employee_YTD('uploads/firstbalfour/employee_YTD_sss.txt', 'SSS');	
				$this->_fb_employee_YTD('uploads/firstbalfour/employee_YTD_phil.txt', 'PhilHealth');	
				$this->_fb_employee_YTD('uploads/firstbalfour/employee_YTD_hdmf.txt', 'Pag-Ibig');
				$this->_fb_batch_entry('uploads/firstbalfour/batch_transaction.txt', 'Batch Entry Transaction');	
				$this->_fb_batch_entry_employee('uploads/firstbalfour/employee_batch.txt', 'Batch Entry of Employee/s');	
				$this->_fb_recurring('uploads/firstbalfour/recurring_transaction.txt', 'Recurring Transaction');	
				$this->_fb_recurring_employee('uploads/firstbalfour/employee_recurring.txt', 'Recurring Transaction of Employee/s');	
				$this->_b4uat_dtr_summary_upload();
			case 'oams':
				$this->_fb_empsetup_employee('uploads/oams/empsetup_201.txt', 'Employee 201');
				$this->_OAM_employee_setup();
			case 'basf':
				$this->_BASF_employee_payroll();
		}
	}

	function _fb_empsetup_daily_payroll(){
		echo 'TABLE: hr_employee_payroll<br/>WHO: Daily Rates<br/>';
		$fdata = file('uploads/firstbalfour/empsetup_daily_payroll.txt');
		$skip = true;
		$count = 0;
		$count_row = 0;
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$count_row++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);
					}
				}

				$id_number = $insert['id_number'];
				unset($insert['id_number']);

				if(isset($insert['salary'])){
					$insert['salary'] = $this->encrypt->encode($insert['salary']);	
				}

				if(isset($insert['minimum_takehome'])){
					$insert['minimum_takehome'] = $this->encrypt->encode($insert['minimum_takehome']);	
				}

				if(isset($insert['bank_id'])){
					switch( $insert['bank_id'] ){
						case 'Banco de Oro':
							$insert['bank_id'] = '2';
							break;
						case 'Bank of the Philippine Islands':
							$insert['bank_id'] = '3';
							break;
						case 'Secuirty Bank':
							$insert['bank_id'] = '9';
							break;
						case 'United Coconut Planters Bank':
							$insert['bank_id'] = '6';
							break;							
						default:
							$insert['bank_id'] = '';
							break;
					}
				}

				if(isset($insert['taxcode_id'])){
					switch( $insert['taxcode_id'] ){
						case 'SM0':
							$insert['taxcode_id'] = 1;
							break;
						case 'SM1':
							$insert['taxcode_id'] = 2;
							break;
						case 'SM2':
							$insert['taxcode_id'] = 3;
							break;
						case 'SM3':
							$insert['taxcode_id'] = 4;
							break;
						case 'SM4':
							$insert['taxcode_id'] = 5;
							break;
						default:
							$insert['taxcode_id'] = '';
							break;
					}
				}

				//set sss, pagibig, philhealth, ecola, whtx
				$insert['sss_mode'] = '5';
				$insert['sss_week'] = '2,4';
				$insert['hdmf_mode'] = '5';
				$insert['hdmf_week'] = '2,4';
				$insert['phic_mode'] = '5';
				$insert['phic_week'] = '2,4';
				$insert['ecola_week'] = '2,4';
				$insert['tax_mode'] = '1';
				$insert['tax_week'] = '2,4';

				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));
				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$check = $this->db->get_where('employee_payroll', array('employee_id' => $emp->employee_id));
					if( $check->num_rows() == 1 ){
						$this->db->update('employee_payroll', $insert, array('employee_id' => $emp->employee_id));
						$count++;
					}
					else if($check->num_rows() == 0){
						$insert['employee_id'] = $emp->employee_id;
						$this->db->insert('employee_payroll', $insert);
						$count++;
					}
					else{
						echo 'Too many payroll data found for user with id number: '.$id_number.'<br/>';
					}
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					echo 'Not found: '.$id_number.'<br/>';
				}
			}
			else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $count_row .' records updated.<br/>';
	}

	function _fb_empsetup_monthly_payroll(){
		echo 'TABLE: hr_employee_payroll<br/>WHO: Monthly Rates<br/>';
		$fdata = file('uploads/firstbalfour/empsetup_monthly_payroll.txt');
		$skip = true;
		$count = 0;
		$total_rows = 0;
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$total_rows++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);
					}
				}

				$id_number = $insert['id_number'];
				unset($insert['id_number']);

				if(isset($insert['location_id']) && !empty($insert['location_id'])){
					if( utf8_encode($insert['location_id']) != 'Parañaque City' ){
						$qry = "SELECT * FROM (`{$this->db->dbprefix}user_location`) WHERE `location` = '{$insert['location_id']}' AND deleted = 0";
						$loc = $this->db->query( $qry );
						if( $loc->num_rows() == 1 ){
							$insert['location_id'] = $loc->row()->location_id;
						}
						else{
							echo 'Cant find location setup for '.$insert['location_id'] . ' '.$this->db->last_query() .'<br/>' ;
						}
					}
					else{
						$insert['location_id'] = 11;
					}
				}

				if(isset($insert['salary'])){
					$insert['salary'] = $this->encrypt->encode($insert['salary']);	
				}

				if(isset($insert['minimum_takehome'])){
					$insert['minimum_takehome'] = $this->encrypt->encode($insert['minimum_takehome']);	
				}

				if(isset($insert['status_id'])){
					switch( $insert['status_id'] ){
						case 'Project Hire':
							$insert['total_year_days'] = 312;
							break;
						default:
							$insert['total_year_days'] = 261;
							break;
					}
					unset($insert['status_id']);
				}

				if(isset($insert['taxcode_id'])){
					switch( $insert['taxcode_id'] ){
						case 'SM0':
							$insert['taxcode_id'] = 1;
							break;
						case 'SM1':
							$insert['taxcode_id'] = 2;
							break;
						case 'SM2':
							$insert['taxcode_id'] = 3;
							break;
						case 'SM3':
							$insert['taxcode_id'] = 4;
							break;
						case 'SM4':
							$insert['taxcode_id'] = 5;
							break;
						default:
							$insert['taxcode_id'] = '';
							break;
					}
				}

				if(isset($insert['payroll_schedule_id'])){
					switch( $insert['payroll_schedule_id'] ){
						case 'Monthly':
							$insert['payroll_schedule_id'] = 4;
							break;
						case 'Semi-monthly':
							$insert['payroll_schedule_id'] = 5;
							break;
					}
				}

				//set sss, pagibig, philhealth, ecola, whtx
				$insert['sss_mode'] = '5';
				$insert['sss_week'] = '2,4';
				$insert['hdmf_mode'] = '5';
				$insert['hdmf_week'] = '2,4';
				$insert['phic_mode'] = '5';
				$insert['phic_week'] = '2,4';
				$insert['ecola_week'] = '2,4';
				$insert['tax_mode'] = '1';
				$insert['tax_week'] = '2,4';

				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));
				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$check = $this->db->get_where('employee_payroll', array('employee_id' => $emp->employee_id));
					if( $check->num_rows() == 1 ){
						$this->db->update('employee_payroll', $insert, array('employee_id' => $emp->employee_id));
						$count++;
					}
					else if($check->num_rows() == 0){
						$insert['employee_id'] = $emp->employee_id;
						$this->db->insert('employee_payroll', $insert);
						$count++;
					}
					else{
						echo 'Too many payroll data found for user with id number: '.$id_number.'<br/>';
					}
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					echo 'Not found: '.$id_number.'<br/>';
				}
			}
			else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $total_rows .' records updated.<br/>';
	}

	function _fb_empsetup_employee( $file, $who ){
		echo '<br/>TABLE: hr_employee<br/>WHO: '.$who.'<br/>';
		$fdata = file($file);
		$skip = true;
		$count = 0;
		$count_row = 0;
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$count_row++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);
					}
				}

				$id_number = $insert['id_number'];
				unset($insert['id_number']);

				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));
				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$this->db->update('employee', $insert,array('employee_id' => $emp->employee_id));
					$count++;
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					echo 'Not found: '.$id_number.'<br/>';
				}
			}
			else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $count_row .' records updated.<br/>';
	}

	function _fb_employee_loans(){
		echo 'TABLE: hr_employee_loan<br/>WHAT: Employee Loans<br/>';
		$fdata = file('uploads/firstbalfour/employee_loan_setup.txt');
		$skip = true;
		$count = 0;
		$total_rows = 0;
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$total_rows++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);
					}
				}
				$id_number = $insert['id_number'];
				unset($insert['id_number']);

				//loan types
				if(isset($insert['loan_id'])){
					switch( $insert['loan_id'] ){
						case 'COMP LOAN':
							$insert['loan_id'] = 8;
							$loan_id = 8;
							break;
						case 'EAGLASS':
							$insert['loan_id'] = 10;
							$loan_id = 10;
							break;
						case 'FPLC':
							$insert['loan_id'] = 7;
							$loan_id = 7;
							break;
						case 'MAJALCO':
							$insert['loan_id'] = 6;
							$loan_id = 6;
							break;
						case 'PAGL':
							$insert['loan_id'] = 5;
							$loan_id = 5;
							break;
						case 'SSS SAL':
							$insert['loan_id'] = 4;
							$loan_id = 4;
							break;
					}
				}

				$insert['loan_status_id'] = 2;
				$insert['interest'] = 0;	 
				$insert['interest_type_id'] = 2;
				$insert['payment_mode_id'] = 1;
				$insert['week'] = '2,4';

				$insert['entry_date'] = date('Y-m-d', strtotime($insert['entry_date']));
				$insert['release_date'] = date('Y-m-d', strtotime($insert['release_date']));
				$insert['start_date'] = date('Y-m-d', strtotime($insert['start_date']));

				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));

				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$check = $this->db->get_where('employee_loan', array('employee_id' => $emp->employee_id, 'loan_id' => $loan_id ));
					if( $check->num_rows() == 1 ){
						$this->db->update('employee_loan', $insert, array('employee_id' => $emp->employee_id, 'loan_id' => $loan_id ));
						$count++;
					}
					else if($check->num_rows() == 0){
						$insert['employee_id'] = $emp->employee_id;
						$this->db->insert('employee_loan', $insert);
						$count++;
					}
					else{
						echo 'Too many payroll data found for user with id number: '.$id_number.'<br/>';
					}
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					echo 'Not found: '.$id_number.'<br/>';
				}
			}
			else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $total_rows .' records updated.<br/>';
	}

	function _fb_employee_YTD( $file, $who){
		echo '<br/>TABLE: hr_employee<br/>WHO: '.$who.'<br/>';
		$fdata = file($file);
		$skip = true;
		$count = 0;
		$total_rows = 0;
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$total_rows++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);
					}
				}

				$id_number = $insert['id_number'];
				unset($insert['id_number']);

				$insert['period_id'] = 1;	
				$insert['processing_type_id'] = 1;	
				$insert['payroll_date'] = '2013-01-01';	
				$insert['actual_payroll_date'] = '2013-01-01';	
				$payroll_date = '2013-01-01';

				if(isset( $who )){
					switch( $who ){
						case 'SSS':
							$insert['transaction_id'] = 49;
							$insert['transaction_code'] = 'SSS_EMP'; 
							$insert['transaction_type_id'] = 5;
							$transaction_id = 49;
							break;
						case 'PhilHealth':
							$insert['transaction_id'] = 50;
							$insert['transaction_code'] = 'PHIC_EMP';
							$insert['transaction_type_id'] = 5;
							$transaction_id = 50;
							break;
						case 'Pag-Ibig':
							$insert['transaction_id'] = 52;
							$insert['transaction_code'] = 'HDMF_EMP';
							$insert['transaction_type_id'] = 5;
							$transaction_id = 52;
							break;
						case 'Withholding Tax':
							$insert['transaction_id'] = 53;
							$insert['transaction_code'] = 'WHTAX';
							$insert['transaction_type_id'] = 3;
							$transaction_id = 53;
							break;
					}
				}

				$insert['quantity'] = 1;	
				$insert['unit_rate'] = 1;
				$insert['inserted_from_id'] = 1;
				$insert['remarks'] = 'YTD Update from Jan-Aug';
				
				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));
				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$check = $this->db->get_where('payroll_closed_transaction', array('employee_id' => $emp->employee_id, 'transaction_id' => $transaction_id, 'payroll_date' => $payroll_date));
					if( $check->num_rows() == 1 ){
						$this->db->update('payroll_closed_transaction', $insert, array('employee_id' => $emp->employee_id, 'transaction_id' => $transaction_id, 'payroll_date' => $payroll_date));
						$count++;
					}
					else if($check->num_rows() == 0){
						$insert['employee_id'] = $emp->employee_id;
						$this->db->insert('payroll_closed_transaction', $insert);
						$count++;
					}
					else{
						echo 'Too many payroll data found for user with id number: '.$id_number.'<br/>';
					}
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					echo 'Not found: '.$id_number.'<br/>';
				}
			}
			else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $total_rows .' records updated.<br/>';
	}

	function _fb_batch_entry( $file, $who){
		echo '<br/>TABLE: hr_payroll_batch_entry;<br/>Transaction: '.$who.'<br/>';
		$fdata = file($file);
		$skip = true;
		$count = 0;
		$total_rows = 0;
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$total_rows++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);
					}
				}

				$transaction_code = $insert['transaction_id'];
				$transaction = $this->db->get_where('payroll_transaction', array('deleted' => 0, 'transaction_code' => $transaction_code))->row();
				$insert['transaction_id'] = $transaction->transaction_id;				
				$insert['payroll_date'] = date('Y-m-d',strtotime($insert['payroll_date']));

				$doc_no = $insert['doc_no'];

				$batch = $this->db->get_where('payroll_batch_entry', array('deleted' => 0, 'doc_no' => $doc_no));
				if( $batch->num_rows() == 0 ){
					$batch = $batch->row();
					$this->db->insert('payroll_batch_entry', $insert);
					$count++;
				}
				else{
					echo 'Document number ('.$doc_no.') is already used by another transaction on batch entry.<br/>';
				}
			}
			else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $total_rows .' records inserted.<br/>';
	}

	function _fb_batch_entry_employee( $file, $who){
		echo '<br/>TABLE: hr_payroll_batch_entry_employee;<br/>Transaction: '.$who.'<br/>';
		$fdata = file($file);
		$skip = true;
		$count = 0;
		$total_rows = 0;
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$total_rows++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);
					}
				}

				$doc_no = $insert['batch_entry_id'];
				$batch_entry = $this->db->get_where('payroll_batch_entry', array('doc_no' => $doc_no))->row();
				$insert['batch_entry_id'] = $batch_entry->batch_entry_id;

				$batch_entry_id = $batch_entry->batch_entry_id;

				$id_number = $insert['id_number'];
				unset($insert['id_number']);


				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));
				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$check = $this->db->get_where('payroll_batch_entry_employee', array('employee_id' => $emp->employee_id, 'batch_entry_id' => $batch_entry_id));
					if( $check->num_rows() == 1 ){
						$this->db->update('payroll_batch_entry_employee', $insert, array('employee_id' => $emp->employee_id, 'batch_entry_id' => $batch_entry_id));
						$count++;
					}
					else if($check->num_rows() == 0){
						$insert['employee_id'] = $emp->employee_id;
						$this->db->insert('payroll_batch_entry_employee', $insert);
						$count++;
					}
					else{
						echo 'Too many payroll data found for user with id number: '.$id_number.'<br/>';
					}
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					echo 'Not found: '.$id_number.'<br/>';
				}
			}
			else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $total_rows .' records updated.<br/>';
	}

	function _fb_recurring( $file, $who){
		echo '<br/>TABLE: hr_payroll_recurring;<br/>Transaction: '.$who.'<br/>';
		$fdata = file($file);
		$skip = true;
		$count = 0;
		$total_rows = 0;
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$total_rows++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);
					}
				}

				$transaction_code = $insert['transaction_id'];
				$transaction = $this->db->get_where('payroll_transaction', array('deleted' => 0, 'transaction_code' => $transaction_code))->row();
				$insert['transaction_id'] = $transaction->transaction_id;

				$payroll_transaction_method = $insert['transaction_method_id'];
				$transaction_method = $this->db->get_where('payroll_transaction_method', array('deleted' => 0, 'payroll_transaction_method' => $payroll_transaction_method))->row();
				$insert['transaction_method_id'] = $transaction_method->payroll_transaction_method_id;
				
				$insert['date_from'] = date('Y-m-d',strtotime($insert['date_from']));
				$insert['date_to'] = date('Y-m-d',strtotime($insert['date_to']));

				$document_no = $insert['document_no'];
				
				$batch = $this->db->get_where('payroll_recurring', array('deleted' => 0, 'document_no' => $document_no));
				if( $batch->num_rows() == 0 ){
					$batch = $batch->row();
					$this->db->insert('payroll_recurring', $insert);
					$count++;
				}
				else{
					echo 'Document number ('.$document_no.') is already used by another transaction on batch entry.<br/>';
				}
			}
			else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $total_rows .' records updated.<br/>';
	}
	
	function _fb_recurring_employee( $file, $who){
		echo '<br/>TABLE: hr_payroll_recurring_employee;<br/>Transaction: '.$who.'<br/>';
		$fdata = file($file);
		$skip = true;
		$count = 0;
		$total_rows = 0;
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$total_rows++;
				$insert = array();

				

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);
					}
				}

				$document_no = $insert['document_no'];
				$recurring = $this->db->get_where('payroll_recurring', array('document_no' => $document_no))->row();
				$insert['recurring_id'] = $recurring->recurring_id;
				$recurring_id = $recurring->recurring_id;

				unset($insert['document_no']);

				$id_number = $insert['id_number'];
				unset($insert['id_number']);

				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));
				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$check = $this->db->get_where('payroll_recurring_employee', array('employee_id' => $emp->employee_id, 'recurring_id' => $recurring_id));
					if( $check->num_rows() == 1 ){
						$this->db->update('payroll_recurring_employee', $insert, array('employee_id' => $emp->employee_id, 'recurring_id' => $recurring_id));
						$count++;
					}
					else if($check->num_rows() == 0){
						$insert['employee_id'] = $emp->employee_id;
						$this->db->insert('payroll_recurring_employee', $insert);
						$count++;
					}
					else{
						echo 'Too many payroll data found for user with id number: '.$id_number.'<br/>';
					}
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					echo 'Not found: '.$id_number.'<br/>';
				}
			}
			else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $total_rows .' records updated.<br/>';
	}

	// balfour uploader
    function _b4uat_dtr_summary_upload(){
    	$fdata = file('uploads/firstbalfour/dtr_upload.txt');
    	$missing_employee = array();
    	$missing_code = array();
    	foreach($fdata as $row){
    		$insert = array();
			
			$row_data = explode("\t", $row);

			foreach( $row_data as $index => $value ){
				$row_data[$index] = trim($value);	
			}

    		$emp = $this->db->get_where('employee', array('id_number' => $row_data[0]));//->row();
    		
    		if( $emp->num_rows() == 1 ){
    			$emp = $emp->row();
	    		$insert['employee_id'] = $emp->employee_id;
	    		$insert['payroll_date'] = date('Y-m-d', strtotime($row_data[1]));
	    		
	    		if( !empty($row_data[2]) ) $insert['hours_worked'] = $row_data[2];
	    		if( !empty($row_data[3]) ) $insert['absences'] = $row_data[3];
	    		if( !empty($row_data[4]) ) $insert['lates'] = $row_data[4];

	    		if( !empty( $row_data[5] ) ){
	    			switch( $row_data[5] ){
	    				case 'COT125';
	    				case 'ROT150';
	    					$insert['reg_ot'] = $row_data[6];
	    					break;
	    				case 'COT100';
	    				case 'COT130';
	    				case 'CT130';
	    				case 'RT150';
	    					$insert['rd_ot'] = $row_data[6];
	    					break;
	    				case 'COT169';
	    				case 'ROT195';
	    				case 'RT195';
	    					$insert['rd_ot_excess'] = $row_data[6];
	    					break;	
	    				case 'CND10';
	    				case 'CND25';
	    				case 'RND25';
	    					$insert['reg_nd'] = $row_data[6];
	    					break;
	    				case 'SOT';
	    					$insert['spe_ot'] = $row_data[6];
	    					break;
	    				default:
	    					if(!in_array( $row_data[5], $missing_code) ) $missing_code[] = $row_data[5];
	    					break;
	    			}
	    		}
	    		
	    		$summary = $this->db->get_where('timekeeping_period_summary', array('employee_id' => $emp->employee_id, 'payroll_date' => $insert['payroll_date']));
	    		if( $summary->num_rows() == 1 ){
	    			$this->db->update('timekeeping_period_summary', $insert, array('employee_id' => $emp->employee_id, 'payroll_date' => $insert['payroll_date']));
	    		}
	    		else{
	    			$this->db->insert('timekeeping_period_summary', $insert);
	    		}
	    	}
	    	else{
	    		if(!in_array( $row_data[0], $missing_employee) ) $missing_employee[] = $row_data[0];
	    	}
    	}
    }
    
    // OAMS
    function _OAM_employee_setup(){
    	echo 'TABLE: hr_employee_payroll<br/>WHO: Payroll Employee Setup<br/>';
		$fdata = file('uploads/oams/empsetup_payroll.txt');
		$skip = true;
		$count = 0;
		$total_rows = 0;
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$total_rows++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);
					}
				}

				$id_number = $insert['id_number'];
				unset($insert['id_number']);

				if(isset($insert['salary'])){
					$insert['salary'] = $this->encrypt->encode($insert['salary']);	
				}

				if(isset($insert['minimum_takehome'])){
					$insert['minimum_takehome'] = $this->encrypt->encode($insert['minimum_takehome']);	
				}

				if(isset($insert['taxcode_id'])){
					switch( $insert['taxcode_id'] ){
						case 'SM0':
							$insert['taxcode_id'] = 1;
							break;
						case 'SM1':
							$insert['taxcode_id'] = 2;
							break;
						case 'SM2':
							$insert['taxcode_id'] = 3;
							break;
						case 'SM3':
							$insert['taxcode_id'] = 4;
							break;
						case 'SM4':
							$insert['taxcode_id'] = 5;
							break;
						default:
							$insert['taxcode_id'] = '';
							break;
					}
				}

				if(isset($insert['payroll_schedule_id'])){
					switch( $insert['payroll_schedule_id'] ){
						case 'Monthly':
							$insert['payroll_schedule_id'] = 4;
							break;
						case 'Semi-monthly':
							$insert['payroll_schedule_id'] = 5;
							break;
					}
				}



				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));
				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$check = $this->db->get_where('employee_payroll', array('employee_id' => $emp->employee_id));
					if( $check->num_rows() == 1 ){
						$this->db->update('employee_payroll', $insert, array('employee_id' => $emp->employee_id));
						$count++;
					}
					else if($check->num_rows() == 0){
						$insert['employee_id'] = $emp->employee_id;
						$this->db->insert('employee_payroll', $insert);
						$count++;
					}
					else{
						echo 'Too many payroll data found for user with id number: '.$id_number.'<br/>';
					}
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					echo 'Not found: '.$id_number.'<br/>';
				}
			}
			else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $total_rows .' records updated.<br/>';
    }

    // BASF
    function _BASF_employee_payroll(){
 		$fdata = file('uploads/BASF/emp_salary.txt');
		$skip = true;
		$count = 0;
		$total_rows = 0;
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$total_rows++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);
					}
				}

				$id_number = $insert['id_number'];
				unset($insert['id_number']);

				if(isset($insert['salary'])){
					$insert['salary'] = $this->encrypt->encode($insert['salary']);	
				}

				$insert['minimum_takehome'] = 0;
				$insert['minimum_takehome'] = $this->encrypt->encode($insert['minimum_takehome']);	
				

				$insert['total_year_days'] = 261;
				$insert['payroll_schedule_id'] = 4;
				$insert['payment_type_id'] = 1;
				$insert['sensitivity'] = 3;

				//set sss, pagibig, philhealth, whtx
				$insert['sss_mode'] = '5';
				$insert['sss_week'] = '2';
				$insert['hdmf_mode'] = '1';
				$insert['hdmf_week'] = '2';
				$insert['phic_mode'] = '1';
				$insert['phic_week'] = '2';
				$insert['tax_mode'] = '2';
				$insert['tax_week'] = '2';

				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));
				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$check = $this->db->get_where('employee_payroll', array('employee_id' => $emp->employee_id));
					if( $check->num_rows() == 1 ){
						$this->db->update('employee_payroll', $insert, array('employee_id' => $emp->employee_id));
						$count++;
					}
					else if($check->num_rows() == 0){
						$insert['employee_id'] = $emp->employee_id;
						$this->db->insert('employee_payroll', $insert);
						$count++;
					}
					else{
						echo 'Too many payroll data found for user with id number: '.$id_number.'<br/>';
					}
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					echo 'Not found: '.$id_number.'<br/>';
				}
			}
			else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $total_rows .' records updated.<br/>';   	
    }

    //asian shipping 201 and payroll uploading
    function asianshipping_uploader(){
    	$this->load->library('encrypt');
    	$this->_as_initial_upload();
    	$this->_as_user_upload();
    	$this->_as_employee_upload();
    	$this->_as_payroll_upload();
    	$this->_as_salary_upload();
    	$this->_as_position_upload();
    }

    private function _as_initial_upload(){
    	echo 'Initial upload to employee and user table<br/>';
		$fdata = file('uploads/asianshipping/employee_employed_date.txt');
		$skip = true;
		$count = 0;
		$count_row = 0;
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$count_row++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);
					}
				}

				$id_number = $insert['id_number'];
				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));
				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$this->db->update('employee', $insert, array('employee_id' => $emp->employee_id));
					$count++;
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					//insert
					$this->db->insert('user', array('login' => $id_number)); 
					$user_id = $this->db->insert_id();
					$this->db->update('user', array('employee_id' => $user_id), array('user_id' => $user_id));
					$insert['user_id'] = $insert['employee_id'] = $user_id;
					$this->db->insert('employee', $insert);
					$this->db->insert('employee_payroll', array('employee_id' => $user_id));
					$count++;
				}					

			}else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $count_row .' records updated.<br/>';	
    }

    private function _as_user_upload(){
    	echo 'Users table<br/>';
		$fdata = file('uploads/asianshipping/user.txt');
		$skip = true;
		$count = 0;
		$count_row = 0;
		
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$count_row++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);

						if( $insert[$index_key[$index]] == '\N' ) $insert[$index_key[$index]] = '';
					}
				}

				if(isset($insert['middlename'])){
					$insert['middleinitial'] = substr($insert['middlename'], 0, 1);
					$insert['middleinitial'] = strtoupper($insert['middleinitial']).'.';
				}
				
				/*if(isset($insert['sex'])){
					switch($insert['sex']){
						case 'M':
							$insert['sex'] = 'male';
							break;
						case 'F':
							$insert['sex'] = 'female';
							break;
					}
				}*/

				if(isset($insert['birth_date'])) $insert['birth_date'] = date('Y-m-d', strtotime($insert['birth_date']));

				/*if(isset($insert['company_id'])){
					switch($insert['company_id']){
						case 'RER':
							$insert['company_id'] = 2;
							break;
						default:
							$insert['company_id'] = 1;
							break;
					}
				}*/
				
				$id_number = $insert['id_number'];
				unset($insert['id_number']);

				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));
				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$this->db->update('user', $insert, array('employee_id' => $emp->employee_id));
					$count++;
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					echo 'Not found: '.$id_number.'<br/>';
				}					

			}else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $count_row .' records updated.<br/>';	
    }

    private function _as_employee_upload(){
    	echo 'Employee table<br/>';
		$fdata = file('uploads/asianshipping/employee.txt');
		$skip = true;
		$count = 0;
		$count_row = 0;
		
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$count_row++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);

						if( $insert[$index_key[$index]] == '\N' ) $insert[$index_key[$index]] = '';
					}
				}

				/*if(isset($insert['civil_status_id'])){
					switch($insert['civil_status_id']){
						case 'S':
							$insert['civil_status_id'] = '1';
							break;
						case 'M':
							$insert['civil_status_id'] = '2';
							break;
					}
				}*/

				if(isset($insert['address3']) && !empty($insert['address3'])){
					$insert['pres_address2'] .= ', '.$insert['address3'];
				}

				if(isset( $insert['address3'] )) unset( $insert['address3'] );
				if(isset( $insert['biometric_id'] ) && empty( $insert['biometric_id'] )) unset( $insert['biometric_id'] );

				$id_number = $insert['id_number'];
				unset($insert['id_number']);

				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));
				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$this->db->update('employee', $insert, array('employee_id' => $emp->employee_id));
					$count++;
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					echo 'Not found: '.$id_number.'<br/>';
				}					

			}else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $count_row .' records updated.<br/>';	
    }

    private function _as_payroll_upload(){
    	echo 'Payroll table<br/>';
		$fdata = file('uploads/asianshipping/payroll.txt');
		$skip = true;
		$count = 0;
		$count_row = 0;
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$count_row++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);

						if( $insert[$index_key[$index]] == '\N' ) $insert[$index_key[$index]] = '';
					}
				}

				/*//default values
				$insert['tax_mode'] = '1';

				if(isset($insert['taxcode_id'])){
					switch($insert['taxcode_id']){
						case 'S':
						case 'M':
							$insert['taxcode_id'] = '1';
							break;
						case 'M1':
							$insert['taxcode_id'] = '2';
							break;
						case 'M2':
							$insert['taxcode_id'] = '3';
							break;
						case 'M3':
							$insert['taxcode_id'] = '4';
							break;
						case 'M4':
							$insert['taxcode_id'] = '5';
							break;
						default:
							$insert['taxcode_id'] = '6';
					}
				}

				if(isset($insert['sss_mode'])){
					switch($insert['sss_mode']){
						case 'Y':
							$insert['sss_mode'] = '5';
							break;
						case 'N':
							$insert['sss_mode'] = '2';
							break;
					}
				}

				if(isset($insert['hdmf_mode'])){
					switch($insert['hdmf_mode']){
						case 'Y':
							$insert['hdmf_mode'] = '5';
							break;
						case 'N':
							$insert['hdmf_mode'] = '2';
							break;
					}
				}

				if(isset($insert['phic_mode'])){
					switch($insert['phic_mode']){
						case 'Y':
							$insert['phic_mode'] = '5';
							break;
						case 'N':
							$insert['phic_mode'] = '2';
							break;
					}
				}

				if(isset($insert['payroll_rate_type_id'])){
					switch($insert['payroll_rate_type_id']){
						case 'DAILY-ASC':
							$insert['payroll_rate_type_id'] = '3';
							$insert['sss_week'] = '1,2,3,4,5';
							$insert['hdmf_week'] = '1,2,3,4,5';
							$insert['phic_week'] = '1,2,3,4,5';
							$insert['tax_week'] = '1,2,3,4,5';
							$insert['ecola_week'] = '1,2,3,4,5';
							$insert['payroll_schedule_id'] = '6';
							break;
						case 'DAILY-RER':
							$insert['payroll_rate_type_id'] = '3';
							$insert['sss_week'] = '1,2,3,4,5';
							$insert['hdmf_week'] = '1,2,3,4,5';
							$insert['phic_week'] = '1,2,3,4,5';
							$insert['tax_week'] = '1,2,3,4,5';
							$insert['ecola_week'] = '1,2,3,4,5';
							$insert['payroll_schedule_id'] = '6';
							break;
						case 'EXTRA-RER':
							$insert['payroll_rate_type_id'] = '3';
							$insert['sss_week'] = '1,2,3,4,5';
							$insert['hdmf_week'] = '1,2,3,4,5';
							$insert['phic_week'] = '1,2,3,4,5';
							$insert['tax_week'] = '1,2,3,4,5';
							$insert['ecola_week'] = '1,2,3,4,5';
							$insert['payroll_schedule_id'] = '6';
							break;
						case 'MGT-ASC':
							$insert['payroll_rate_type_id'] = '2';
							$insert['sss_week'] = '2,4';
							$insert['hdmf_week'] = '2,4';
							$insert['phic_week'] = '2,4';
							$insert['tax_week'] = '2,4';
							$insert['ecola_week'] = '2,4';
							$insert['payroll_schedule_id'] = '5';
							break;
						case 'MGT-RER':
							$insert['payroll_rate_type_id'] = '2';
							$insert['sss_week'] = '2,4';
							$insert['hdmf_week'] = '2,4';
							$insert['phic_week'] = '2,4';
							$insert['tax_week'] = '2,4';
							$insert['ecola_week'] = '2,4';
							$insert['payroll_schedule_id'] = '5';
							break;
						case 'MONTH-ASC':
							$insert['payroll_rate_type_id'] = '2';
							$insert['sss_week'] = '2,4';
							$insert['hdmf_week'] = '2,4';
							$insert['phic_week'] = '2,4';
							$insert['tax_week'] = '2,4';
							$insert['ecola_week'] = '2,4';
							$insert['payroll_schedule_id'] = '5';
							break;
						default:
							dbug($insert);
					}
				}
*/
				if(isset($insert['allotment_amount']) && !empty($insert['allotment_amount'])){
					$insert['allotment_mode'] = 2;
				}


				$id_number = $insert['id_number'];
				unset($insert['id_number']);

				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));
				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$check = $this->db->get_where('employee_payroll', array('employee_id' => $emp->employee_id));
					if( $check->num_rows() == 1 ){
						if( sizeof($insert) > 0 ){
							$this->db->update('employee_payroll', $insert, array('employee_id' => $emp->employee_id));
							$count++;
						}
					}
					else if($check->num_rows() == 0){
						$insert['employee_id'] = $emp->employee_id;
						$this->db->insert('employee_payroll', $insert);
						$count++;
					}
					else{
						echo 'Too many payroll data found for user with id number: '.$id_number.'<br/>';
					}
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					echo 'Not found: '.$id_number.'<br/>';
				}					

			}else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $count_row .' records updated.<br/>';	
    }

    private function _as_salary_upload(){
    	echo 'Salary table<br/>';
		$fdata = file('uploads/asianshipping/salary.txt');
		$skip = true;
		$count = 0;
		$count_row = 0;
		$this->load->library('encrypt');
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$count_row++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);

						if( $insert[$index_key[$index]] == '\N' ) $insert[$index_key[$index]] = '';
					}
				}

				$insert['salary'] = $this->encrypt->encode( $insert['salary'] );
				$insert['minimum_takehome'] = $this->encrypt->encode( 0 );

				$id_number = $insert['id_number'];
				unset($insert['id_number']);

				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));
				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$check = $this->db->get_where('employee_payroll', array('employee_id' => $emp->employee_id));
					if( $check->num_rows() == 1 ){
						if( sizeof($insert) > 0 ){
							$this->db->update('employee_payroll', $insert, array('employee_id' => $emp->employee_id));
							$count++;
						}
					}
					else if($check->num_rows() == 0){
						$insert['employee_id'] = $emp->employee_id;
						$this->db->insert('employee_payroll', $insert);
						$count++;
					}
					else{
						echo 'Too many payroll data found for user with id number: '.$id_number.'<br/>';
					}
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					echo 'Not found: '.$id_number.'<br/>';
				}					

			}else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $count_row .' records updated.<br/>';	
    }

    private function _as_position_upload(){
    	echo 'Position table<br/>';
		$fdata = file('uploads/asianshipping/position.txt');
		$skip = true;
		$count = 0;
		$count_row = 0;
		$this->load->library('encrypt');
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$count_row++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);

						if( $insert[$index_key[$index]] == '\N' ) $insert[$index_key[$index]] = '';
					}
				}

				if( !empty( $insert['position_code'] ) ){
					if( is_int( $insert['position_code'] ) )
					{
						$insert['position_id'] = $insert['position_code'];
					}
					else{
						$position = $this->db->get_where('user_position', array('position_code' => $insert['position_code']))->row();
						$insert['position_id'] = $position->position_id;	
					}
					
				}
				unset($insert['position_code']);
				

				$id_number = $insert['id_number'];
				unset($insert['id_number']);

				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));
				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$check = $this->db->get_where('user', array('employee_id' => $emp->employee_id));
					if( $check->num_rows() == 1 ){
						if( sizeof($insert) > 0 ){
							$this->db->update('user', $insert, array('employee_id' => $emp->employee_id));
							$count++;
						}
					}
					else if($check->num_rows() == 0){
						$insert['employee_id'] = $emp->employee_id;
						$this->db->insert('user', $insert);
						$count++;
					}
					else{
						echo 'Too many payroll data found for user with id number: '.$id_number.'<br/>';
					}
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					echo 'Not found: '.$id_number.'<br/>';
				}					

			}else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $count_row .' records updated.<br/>';	
    }

    function basf_uploader(){
    	$this->load->library('encrypt');
    	$this->_basf_employee_setup();
    }

    private function _basf_employee_setup(){
    	$fdata = file('uploads/basf/employee_setup.txt');
		$skip = true;
		$count = 0;
		$count_row = 0;
		foreach($fdata as $row){
			$data = explode("\t", $row);
			if(!$skip){
				$count_row++;
				$insert = array();

				foreach( $data as $index => $value ){
					if( !empty($index_key[$index]) ){
						$insert[$index_key[$index]] = trim($value);
						$insert[$index_key[$index]] = str_replace('"', '', $insert[$index_key[$index]]);

						if( $insert[$index_key[$index]] == '\N' ) $insert[$index_key[$index]] = '';
					}
				}

				//default values
				$insert['tax_mode'] = '2';
				$insert['tax_week'] = '2';
				$insert['sss_mode'] = '5';
				$insert['sss_week'] = '2';
				$insert['hdmf_mode'] = '1';
				$insert['hdmf_week'] = '2';
				$insert['phic_mode'] = '1';
				$insert['phic_week'] = '2';


				if(isset($insert['taxcode_id'])){
					switch($insert['taxcode_id']){
						case 'S':
						case 'HF':
						case 'ME':
							$insert['taxcode_id'] = '1';
							break;
						case 'ME1':
						case 'HF1':
							$insert['taxcode_id'] = '2';
							break;
						case 'ME2':
							$insert['taxcode_id'] = '3';
							break;
						case 'ME3':
							$insert['taxcode_id'] = '4';
							break;
						case 'ME4':
							$insert['taxcode_id'] = '5';
							break;
						default:
							$insert['taxcode_id'] = '6';
					}
				}
				
				$insert['salary'] = $this->encrypt->encode( $insert['salary'] );
				$insert['minimum_takehome'] = $this->encrypt->encode( 0 );
				
				$id_number = $insert['id_number'];
				unset($insert['id_number']);

				$emp = $this->db->get_where('employee', array('deleted' => 0, 'id_number' => $id_number));
				if( $emp->num_rows() == 1 ){
					$emp = $emp->row();
					$check = $this->db->get_where('employee_payroll', array('employee_id' => $emp->employee_id));
					if( $check->num_rows() == 1 ){
						$this->db->update('employee_payroll', $insert, array('employee_id' => $emp->employee_id));
						$count++;
						$found[] = $id_number;
					}
					else if($check->num_rows() == 0){
						$insert['employee_id'] = $emp->employee_id;
						$this->db->insert('employee_payroll', $insert);
						$count++;
						$found[] = $id_number;
					}
					else{
						echo 'Too many payroll data found for user with id number: '.$id_number.'<br/>';
					}
				}
				else if($emp->num_rows() > 1){
					echo 'Too many data found for user with id number : '.$id_number.'<br/>';
				}
				else{
					echo 'Not found: '.$id_number.'<br/>';
					$not_found[] = $id_number;
				}					

			}else{
				foreach( $data as $index => $value ){
					if( !empty($value) ) $index_key[$index] = trim($value);
				} 
			}
			$skip = false;
		}
		echo $count .'/'. $count_row .' records updated.<br/>';
		echo 'Found: ' . implode(',', $found) . '<br/>';
		echo 'Not-found: ' . implode(',', $not_found) . '<br/>';
    }

    function pioneer_dtr_upload(){
    	$fdata = file('uploads/pioneer/dtrtoupload.txt');
		$skip = true;
		$count = 0;
		$count_row = 0;
		foreach($fdata as $row){
			$data = explode("\t", $row);

			$count_row++;
			$insert = array();

			$result = $this->db->get_where('user',array('login' => $data[0]));

			if ($result && $result->num_rows() > 0){
				$row = $result->row();
				$employee_id = $row->employee_id;
				$date = date('Y-m-d',strtotime($data[1]));

				$insert['location_id'] = 1;
				$insert['employee_id'] = $employee_id;
				$insert['date'] = $date;
				$insert['time_in1'] = date('Y-m-d H:i:s',strtotime($data[1] .' '. $data[2]));
				$insert['time_out1'] = date('Y-m-d H:i:s',strtotime($data[1] .' '. $data[3]));

				$this->db->where('deleted',0);
				$this->db->where('employee_id',$employee_id);
				$this->db->where('date',$date);
				$result = $this->db->get('employee_dtr');

				if ($result && $result->num_rows() > 0){
					$this->db->where('employee_id',$employee_id);
					$this->db->where('date',$date);
					$this->db->update('employee_dtr',$insert);
				}
				else{
					$this->db->insert('employee_dtr',$insert);
				}
			}	
			$count++;			
		}
		echo $count .' records updated.<br/>';
    }    
}