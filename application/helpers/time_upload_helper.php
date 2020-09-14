<?php
function check1(){
	$ci=& get_instance();
	$user_id = $ci->userinfo['user_id'];
	process_time_raw_oams(1,$user_id);
}

function check2(){
	$ci=& get_instance();
	$user_id = $ci->userinfo['user_id'];
	process_time_raw(1,$user_id);
}

function check3() {
	$ci=& get_instance();
	$user_id = $ci->userinfo['user_id'];
	process_time_biometrics(1,$user_id);
}
function do_time_upload()
{
	$biometric_id_array = array();
	$date_R = array();

	$ci=& get_instance();

	$location_id = $ci->locationinfo['location_id'];

	$timekeeping_location = $ci->db->get_where('timekeeping_location', array('location_id' => $location_id))->row();

	$config['upload_path'] = $timekeeping_location->folder_location;
	switch( $ci->locationinfo['location_id'] ){
		case 1:
			$type = '1';
			break;
		case 2:
			$type = '2';
			break;
		case 4:
			$type = '3';
			break;
		case 5:
			$type = '5';
			break;			
		case 6:
			$type = '4'; //generic fixed length
			break;
		default:
			$type = 'default';
			break;
	}


	$user_id = $ci->userinfo['user_id'];

	$dir = $ci->locationinfo['full_path'];
	$file = file($dir);
	$dates = array();
	if ($file != false)
	{
		$insert = array();
		switch( $type ){
			//Temporary disable due to work from home manuall dtr entry due o NCOV-19  20200325 - Tirso
/*			case 1:
				//for .txt
				$count = count($file)-1;
				$date_R = array();

				// create log
				$folder = 'logs';
				if(!file_exists($folder)) 
					mkdir($folder, 0777, true);

				$log_file = 'logs/'.date('Ymd_Gis').'.txt';

				$records_read = 0;

				$log_msg = date('Ymd G:i:s')." START UPLOADING \r\n";

				$log_msg .= $count."record(s) read. \r\n";

				foreach ($file as $index => $val)
				{
					if($index != 0)
					{
						$do_not_save = false;
						$val_exp 		= explode("\t", $val);

						$biometric_id 	= str_replace('"', '', $val_exp[2]);

						// Store in array to prevent duplicate query for employee id.
						if (!array_key_exists($biometric_id, $biometric_id_array)) {
							$ci->db->where('biometric_id', $biometric_id);
							$ci->db->where('resigned', 0);
							$ci->db->where('deleted', 0);

							$employee = $ci->db->get('employee');

							if ($employee->num_rows() > 0) {
								$biometric_id_array[$biometric_id] = $employee->row()->employee_id;
							} else {
								if(!in_array($biometric_id, $not_exist_biometrics))
									$not_exist_biometrics[] = $biometric_id;
								$do_not_save = true;
							}
						} 
						
						$employee_id = $biometric_id_array[$biometric_id];
						$date 		 = date("Y-m-d",strtotime($val_exp[3]));
						$checktime	 = date("Y-m-d G:i:s",strtotime($val_exp[3]));
						$checktype	 = str_replace('"', '', $val_exp[4]);

						$where = array(
							'employee_id' => $employee_id,
							'date' => $date,
							'checktime' => $checktime,
							'checktype' => $checktype
						);

						$exist_data = $ci->db->get_where('employee_dtr_raw', $where);

						if ($exist_data->num_rows() == 0) 
							$insert[] = "('$employee_id','{$date}','{$checktime}','{$checktype}','{$location_id}')";

						
						$date_R[] = strtotime($date);

						$records_read++;
					}
				}

				$insert = implode(', ', $insert);
				$log_msg .= implode(" Does not exist \r\n", $not_exist_biometrics);

				$log_msg .= "END UPLOADING";

				write_file($log_file, $log_msg, 'w+');
				break;*/
			case 1:
				//for .txt
				$count = count($file)-1;
				$date_R = array();

				// create log
				$folder = 'logs';
				if(!file_exists($folder)) 
					mkdir($folder, 0777, true);

				$log_file = 'logs/'.date('Ymd_Gis').'.txt';

				$records_read = 0;

				$log_msg = date('Ymd G:i:s')." START UPLOADING \r\n";

				$log_msg .= $count."record(s) read. \r\n";

				foreach ($file as $index => $val)
				{
					$do_not_save = false;
					$val_exp 		= explode(",", $val);

					$biometric_id 	= str_replace('"', '', $val_exp[0]);

					// Store in array to prevent duplicate query for employee id.
					if (!array_key_exists($biometric_id, $biometric_id_array)) {
						$ci->db->where('biometric_id', $biometric_id);
						$ci->db->where('resigned', 0);
						$ci->db->where('deleted', 0);

						$employee = $ci->db->get('employee');

						if ($employee->num_rows() > 0) {
							$biometric_id_array[$biometric_id] = $employee->row()->employee_id;
						} else {
							if(!in_array($biometric_id, $not_exist_biometrics))
								$not_exist_biometrics[] = $biometric_id;
							$do_not_save = true;
						}
					} 
					
					$employee_id = $biometric_id_array[$biometric_id];
					$date 		 = date("Y-m-d",strtotime($val_exp[1]));
					$time_in1	 = date("Y-m-d G:i:s",strtotime($val_exp[1]));
					$time_out1	 = date("Y-m-d G:i:s",strtotime($val_exp[2]));

					$where = array(
						'employee_id' => $employee_id,
						'date' => $date,
						'time_in1' => $time_in1,
						'time_out1' => $time_out1
					);

					$exist_data = $ci->db->get_where('employee_dtr', $where);

					if ($exist_data->num_rows() == 0) 
						$ci->db->insert('employee_dtr', $where);
				}

				$insert = implode(', ', $insert);
				$log_msg .= implode(" Does not exist \r\n", $not_exist_biometrics);

				$log_msg .= "END UPLOADING";

				write_file($log_file, $log_msg, 'w+');

				return;

				break;				
			case 2:
				//for .dat
				$count = count($file)-1;
				$date_R = array();
				foreach ($file as $index => $val)
				{
					$val_exp 		= explode("\t", $val);
					$biometric_id 	= str_replace(' ', '', $val_exp[0]);
					
					// Store in array to prevent duplicate query for employee id.
					if (!in_array($biometric_id, $biometric_id_array)) {
						$ci->db->where('biometric_id', $biometric_id);
						$ci->db->where('resigned', 0);
						$ci->db->where('deleted', 0);

						$employee = $ci->db->get('employee');

						if ($employee->num_rows() > 0) {
							$biometric_id_array[$biometric_id] = $employee->row()->employee_id;
						}
					} 

					if( isset($biometric_id_array[$biometric_id]) ){
						$employee_id = $biometric_id_array[$biometric_id];

						
						$date 			= date("Y-m-d",strtotime($val_exp[1]));
						$checktime		= date("Y-m-d G:i:s",strtotime($val_exp[1]));
						$checktype		= str_replace('"', '', $val_exp[3]);

						if($checktype == 0)
							$checktype = 'C/In';
						
						if($checktype == 1)
							$checktype = 'C/Out';

						$where = array(
							'employee_id' => $employee_id,
							'date' => $date,
							'checktime' => $checktime,
							'checktype' => $checktype
						);

						$exist_data = $ci->db->get_where('employee_dtr_raw', $where);

						if ($exist_data->num_rows() == 0) 
							$insert[] = "('$employee_id','{$date}','{$checktime}','{$checktype}','{$location_id}')";
													
						}
					$date_R[] = strtotime($date);
				}
				$insert = implode(', ', $insert);
				break;
			case 3:
				//for .txt
				$count = count($file)-1;
				$date_R = array();
				foreach ($file as $index => $val)
				{
					$cnt=17;
					$value_checker = trim(substr($val, $cnt, 4));
					$insert_set = 0;
					if(is_numeric($value_checker))
					{

						$ci->db->order_by('sequence');
						$ci->db->where('location_id', $location_id);
						$time_temp = $ci->db->get('timekeeping_template');
						foreach ($time_temp->result() as $key => $value) {
							$x = $value->id_name;
							$$x = $value->length;
						}
						$biometric_id 	= trim(substr($val, $cnt, $id));
						$cnt = $cnt+$id;
						$date_get = substr($val, $cnt, $date_time);
						$cnt = $cnt+$date_time;
						$identifier = trim(substr($val, $cnt, $identifier));
						// Store in array to prevent duplicate query for employee id.
						if (!array_key_exists($biometric_id, $biometric_id_array)) {
							$ci->db->where('biometric_id', $biometric_id);
							$ci->db->where('resigned', 0);
							$ci->db->where('deleted', 0);

							$employee = $ci->db->get('employee');
							if ($employee->num_rows() > 0) {
								$biometric_id_array[$biometric_id] = $employee->row()->employee_id;
							}
							// else
							// {
							// 	dbug($biometric_id.' == '.date("Y-m-d",strtotime($date_get)).' == '.$date_get);
							// }
						} 				
						$employee_id = $biometric_id_array[$biometric_id];
						$date 		 = date("Y-m-d",strtotime($date_get));
						$checktime	 = date("Y-m-d G:i:s",strtotime($date_get));
						$checktype	 = $identifier;					
						
						$date_R[] = strtotime($date);
						$insert_set = 1;
					}
					$tag = 0;
					if($index == $count)				
					{
						$ending_insert = trim($val);
						if(!empty($ending_insert))
						{
							$tag = 1;
						}
						else
						{
							$length_insert = strlen($insert) - 1;
							$last_string = substr($insert, $length_insert,1);
							if($last_string == ',')
							{
								$insert = substr($insert, 0, $length_insert);
							}						
						}
					}
					if($insert_set)
					{
						$where = array(
							'employee_id' => $employee_id,
							'date' => $date,
							'checktime' => $checktime,
							'checktype' => $checktype
						);

						$exist_data = $ci->db->get_where('employee_dtr_raw', $where);

						if ($exist_data->num_rows() == 0)
							$insert[] = "('$employee_id','{$date}','{$checktime}','{$checktype}','{$location_id}')";	
					}
				}
				$insert = implode(', ', $insert);
				break;
			case 4: //generic fixed length
				$ci->db->order_by('sequence');
				$ci->db->where('location_id', $location_id);
				$params = $ci->db->get('timekeeping_template')->result_array();
				foreach($file as $row){
					$pointer = 0;
					foreach( $params as $param ){
						$value = substr ( $row, $pointer, $param['length'] );
						if($param['datatype'] == 'datetime'){
							$year = substr ($value, 0, 4 );
							$month = substr ($value, 4, 2 );
							$day = substr ($value, 6, 2 );
							$hour = substr ($value, 8, 2 );
							$minute = substr ($value, 10, 2 );

							$date = $year .'-' . $month .'-'. $day;
							$value = $year .'-' . $month .'-'. $day . ' ' . $hour . ':' . $minute;
							$date_R[] = strtotime($value);
						}

						if($param['table_fieldsource'] == 'employee_id'){
							if (!array_key_exists($value, $biometric_id_array)) {
								$ci->db->where('biometric_id', $value);
								$ci->db->where('resigned', 0);
								$ci->db->where('deleted', 0);

								$employee = $ci->db->get('employee');
								if ($employee->num_rows() > 0) {
									$biometric_id_array[$value] = $employee->row()->employee_id;
								}
							}
							$value = $biometric_id_array[$value];
						}

						if($param['table_fieldsource'] == 'checktype'){
							switch($value){
								case '01':
									$value = 'C/In';
									break;
								case '02':
									$value = 'C/Out';
									break;
								case '03':
									$value = 'B/Out';
									break;
								case '04':
									$value = 'B/In';
									break;
							}
						}

						$$param['table_fieldsource'] = $value;
						$pointer += $param['length'];
					}

					$where = array(
							'employee_id' => $employee_id,
							'date' => $date,
							'checktime' => $checktime,
							'checktype' => $checktype
						);

					$exist_data = $ci->db->get_where('employee_dtr_raw', $where);

					if ($exist_data->num_rows() == 0) 
						$insert[] = "('$employee_id','{$date}','{$checktime}','{$checktype}','{$location_id}')";

				}

				$insert = implode(', ', $insert);
				break;
			case 5:
				//for .xls
				$ci->load->library('PHPExcel');

				$objReader = new PHPExcel_Reader_Excel5;

				if (!$objReader) {
					show_error('Could not get reader.');
				}

				$objReader->setReadDataOnly(true);
				$objPHPExcel = $objReader->load( $dir );
				$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
			
				$ctr = 0;	
				$import_data = array();
				foreach($rowIterator as $row){

					$cellIterator = $row->getCellIterator();
					$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
					
					$rowIndex = $row->getRowIndex();
					
					// Build the array to insert and check for validation errors as well.
					foreach ($cellIterator as $cell) {
						$import_data[$ctr][] = $cell->getCalculatedValue();
					}

					if ($rowIndex == 1) {
						unset($import_data[$ctr]);
					}

					$ctr++;
				}

				$date_R = array();
				$ctr = 0;
				// Remove non-matching cells.
				foreach ($import_data as $row) {
					$id_number = $row[0];

					$result = $ci->db->get_where('employee',array('biometric_id' => $id_number));
					if ($result && $result->num_rows() > 0){
						$employee_id = $result->row()->employee_id;

						$date 		 = PHPExcel_Style_NumberFormat::toFormattedString($row[1],PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
						$time_in1	 = PHPExcel_Style_NumberFormat::toFormattedString ($row[1], 'yyyy-mm-dd hh:mm:ss');
						$time_out1	 = PHPExcel_Style_NumberFormat::toFormattedString ($row[2], 'yyyy-mm-dd hh:mm:ss');

						$date = (date('Y-m-d',strtotime(substr($row[1], 0, -3))));
						$time_in1 = date('Y-m-d H:i:s',strtotime(substr($row[1], 0, -3)));
						$time_out1 = date('Y-m-d H:i:s',strtotime(substr($row[2], 0, -3)));

						//$date = PHPExcel_Style_NumberFormat::toFormattedString($row[3],PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
						//$time = PHPExcel_Style_NumberFormat::toFormattedString($row[3], 'hh:mm:ss');

						$where = array(
							'employee_id' => $employee_id,
							'date' => $date
						);

						$to_insert = array(
							'employee_id' => $employee_id,
							'date' => $date,
							'time_in1' => $time_in1,
							'time_out1' => $time_out1,
							'upload_by' => 1
						);						

						$to_update = array(
							'time_in1' => $time_in1,
							'time_out1' => $time_out1,
							'upload_by' => 1
						);	

						$exist_data = $ci->db->get_where('employee_dtr', $where);

						if ($exist_data->num_rows() == 0) 
							$ci->db->insert('employee_dtr', $to_insert);
						else {
							$ci->db->where($where);
							$ci->db->update('employee_dtr', $to_update);
						}												

						$ctr++;
					}
				}

				$insert = implode(', ', $insert);
				$log_msg .= implode(" Does not exist \r\n", $not_exist_biometrics);

				$log_msg .= "END UPLOADING";

				write_file($log_file, $log_msg, 'w+');

				$return['count'] = $ctr;

				return $return;

				break;					
			default:
				
				break;
		}

		$lowest = date("Y-m-d",min($date_R));
		$highest = date("Y-m-d",max($date_R));

		$dates = array_unique($date_R);

		foreach( $dates as $index => $date ){
			$dates[$index] = date('Y-m-d', $date);
		}

		if(!empty($insert)){
			$qry_dtr = "INSERT INTO `{$ci->db->dbprefix}employee_dtr_raw` (`employee_id`, `date`, `checktime`, `checktype`, `location_id`) VALUES".$insert;
			$ci->db->query($qry_dtr);
		}
	}

	// switch(CLIENT_DIR){
	// 	case 'oams':
	// 		process_time_raw_with_timeline($location_id, $user_id);
	// 		break;
	// 	case 'asianshipping':
	// 		as_process_time_raw($location_id, $user_id);
	// 		break;
	// 	default:
			process_time_biometrics($location_id,$user_id,$dates );
	// }

	$return['lowest'] = $lowest;
	$return['highest'] = $highest;
	$return['count'] = sizeof($file);
	$return['user'] = $user_id;
	return $return;
}

function do_time_upload_lotus_notes()
{
	$biometric_id_array = array();

	$ci=& get_instance();
	$location_id = $ci->locationinfo['location_id'];

	$timekeeping_location = $ci->db->get_where('timekeeping_location', array('location_id' => $location_id))->row();

	$config['upload_path'] = $timekeeping_location->folder_location;

	$user_id = $ci->userinfo['user_id'];

	$dir = $ci->locationinfo['full_path'];
	$file = file($dir);
	if ($file != false)
	{
		$insert='';
		//for .txt lotus notes
		$ctr = 0;	
		$count = count($file) - 1;
		$date_R = array();
		foreach ($file as $index => $val)
		{
			$val_exp 		= explode(", ", $val);
			$biometric_id 	= $val_exp[0];

			// Store in array to prevent duplicate query for employee id.
			if (!array_key_exists($biometric_id, $biometric_id_array)) {
				$ci->db->where('biometric_id', $biometric_id);
				$ci->db->where('resigned', 0);
				$ci->db->where('deleted', 0);

				$employee = $ci->db->get('employee');

				if ($employee->num_rows() > 0) {
					$biometric_id_array[$biometric_id] = $employee->row()->employee_id;
				}
			} 
			
			$employee_id   = $biometric_id_array[$biometric_id];
			$date 		   = date("Y-m-d",strtotime($val_exp[1]));
			$timein 	   = date("G:i:s",strtotime($val_exp[3]));
			$timeout 	   = date("G:i:s",strtotime($val_exp[4]));
			$checktimein   = $date .' '. $timein;
			$checktimeout  = $date .' '. $timeout;
			$checktype_in  = "C/In";
			$checktype_out = "C/Out";
			$datetimein = $val_exp[3];
			$datetimeout = $val_exp[4];
			$unixtimein = strtotime( $datetimein );
			$unixtimeout = strtotime( $datetimeout );

			if ( FALSE == $unixtimein )
			{
			    $checktimein = '';
			}  

			if ( FALSE == $unixtimeout )
			{
			    $checktimeout = '';
			}  

			if ($employee_id != '' && $date != '')
			{
				$where_in = array(
					'employee_id' => $employee_id,
					'date' => $date,
					'checktime' => $checktimein,
					'checktype' => $checktype_in 
				);
				$raw_in = $ci->db->get_where('employee_dtr_raw', $where_in);
				
				$where_out = array(
					'employee_id' => $employee_id,
					'date' => $date,
					'checktime' => $checktimeout,
					'checktype' => $checktype_out 
				);
				$raw_out = $ci->db->get_where('employee_dtr_raw', $where_out);

				if($index == $count)
				{
					if ($raw_in->num_rows() == 0) $insert .= "('$employee_id','{$date}','{$checktimein}','{$checktype_in}','{$location_id}'),";	
					if ($raw_out->num_rows() == 0) $insert .= "('$employee_id','{$date}','{$checktimeout}','{$checktype_out}','{$location_id}')";	
				}
				else
				{
					if ($raw_in->num_rows() == 0) $insert .= "('$employee_id','{$date}','{$checktimein}','{$checktype_in}','{$location_id}'),";	
					if ($raw_out->num_rows() == 0) $insert .= "('$employee_id','{$date}','{$checktimeout}','{$checktype_out}','{$location_id}'),";
				}
				
				$date_R[] = strtotime($date);
				$ctr++;				
			}
		}
		
		if (count($date_R) > 0)
		{
			$lowest = date("Y-m-d",min($date_R));
			$highest = date("Y-m-d",max($date_R));
		}
		else
		{
			$lowest = 0;
			$highest = 0;			
		}

		if ($insert != ''){
			$qry_dtr = "INSERT INTO `{$ci->db->dbprefix}employee_dtr_raw` (`employee_id`, `date`, `checktime`, `checktype`, `location_id`) VALUES".$insert;
			//dbug($qry_dtr);
			$ci->db->query($qry_dtr);	

			process_time_raw_lotus_notes($location_id,$user_id);
			$return['lowest'] = $lowest;
			$return['highest'] = $highest;
			$return['count'] = $ctr;
			$return['user'] = $user_id;
			return $return;			
		}		
	}
}

function process_time_raw_lotus_notes($location_id,$user_id)
{
	$ci=& get_instance();

	$dtr_raw = $ci->db->get_where('employee_dtr_raw', array('location_id' => $location_id, 'processed' => 0));
	$o_dtr_raw = $dtr_raw->result();

	$e_dtr = array();	

	foreach ($o_dtr_raw as $dtr_entry) {		
		$e_dtr[$dtr_entry->employee_id.$dtr_entry->date]['date'] = $dtr_entry->date;
		$e_dtr[$dtr_entry->employee_id.$dtr_entry->date]['employee_id'] = $dtr_entry->employee_id;
		if ($dtr_entry->checktype == 'C/In') {
			$e_dtr[$dtr_entry->employee_id.$dtr_entry->date]['in'] = $dtr_entry->checktime;
		} else {
			$e_dtr[$dtr_entry->employee_id.$dtr_entry->date]['out'] = $dtr_entry->checktime;
		}		
	}

	foreach ($e_dtr as $employee_id => $employee_day_record) {	
		$ci->db->where('employee_id', $employee_day_record['employee_id']);
		$ci->db->where('date', $employee_day_record['date']);
		$ci->db->where('deleted', 0);

		$result = $ci->db->get('employee_dtr');

		$employee_dtr_row = array(
				'location_id' => $location_id,
				'date' => $employee_day_record['date'],
				'time_in1' => $employee_day_record['in'],
				'time_out1' => $employee_day_record['out'],
				'upload_by' => $user_id		
				);

		if ($result->num_rows() > 0) {
			$entry = $result->row();
			$ci->db->where('id', $entry->id);
			$ci->db->update('employee_dtr', $employee_dtr_row);
		} else {
			$employee_dtr_row['employee_id'] = $employee_day_record['employee_id'];
			$ci->db->insert('employee_dtr', $employee_dtr_row);
		}	
	}
}

function dailyrecordstableproc($periodfrom, $periodto, $id, $isproc = false) 
{
	$sql = 'SELECT f.id compid, g.id deptid, a.employee_id, a.id_number, a.group_id,';

    if (!$isproc) {
    	$sql .= "f.name compname, g.name deptname, CONCAT(b.last_name,', ',b.first_name)employee_name,";
    }
    
    $sql .= "CONCAT(DATE_FORMAT(c.schedule_date,'%d'),' ',LEFT(DAYNAME(c.schedule_date),2))daysched,
       c.id schedule_id, c.schedule_date, c.sched_type, c.shift_id,
       d.name, d.start_time, d.end_time,
       
       CAST(CONCAT(c.schedule_date,' ',CAST(d.start_time AS TIME)) AS DATETIME)estarttime,
       CAST(CONCAT(c.schedule_date,' ',CAST(d.end_time AS TIME)) AS DATETIME)edatetime,

       a.flexible_shift,
       
       e.time_in1, e.time_out1,
       e.time_in2, e.time_out2,

       IFNULL(c.day_absent,0) empabsent,
       IFNULL(c.day_leave,0) empleave,
       IFNULL(c.day_lwop,0) emplwop,

       IFNULL(c.hrs_ot,0) empovertime,
       IFNULL(c.min_late,0) emplates,
       IFNULL(c.min_undertime,0) empundertime,
       IFNULL(c.min_late_actual,0) emplatesactual,

       c.remarks,
       c.is_locked,

       (
       SELECT COUNT(ef.id)
       FROM {$ci->db->dbprefix}employee_forms ef
       WHERE IF(ef.form_type = 1, c.schedule_date=ef.focus_date, c.schedule_date BETWEEN ef.focus_date AND ef.date_to) AND
             ef.employee_id = a.employee_id AND
             IFNULL(ef.is_cancel,0) = 0 AND (ef.approved1 <> 3 AND ef.approved2 <> 3)             
       ) formcount,
       
       h.date_set,
       IFNULL(h.legal_holiday,0)legal_holiday,
       h.params

   FROM {$ci->db->dbprefix}employee a
   LEFT JOIN {$ci->db->dbprefix}users b ON b.employee_id=a.employee_id
   LEFT JOIN {$ci->db->dbprefix}employee_schedule c ON c.employee_id=a.employee_id
   LEFT JOIN {$ci->db->dbprefix}shifts d ON d.id=c.shift_id
   LEFT JOIN {$ci->db->dbprefix}employee_dtr e ON e.employee_id=a.biometric_id AND c.schedule_date=e.date
   LEFT JOIN {$ci->db->dbprefix}company f ON f.id = a.company_id
   LEFT JOIN {$ci->db->dbprefix}department g ON g.id = a.department_id
   LEFT JOIN {$ci->db->dbprefix}holidays h ON h.date_set = c.schedule_date AND IFNULL(h.inactive,0)=0 ";

   	$sql .= 'WHERE a.employee_id = ' . mysql_real_escape_string($id);
   	$sql .=  " AND (c.schedule_date BETWEEN '" . $periodfrom . "' AND '" . $periodto . "') AND ";
	$sql .= "IF(IFNULL(a.resigned_date,0)=0, 1, a.resigned_date >= '" . $periodfrom . "' )";
	$sql .= " ORDER BY f.name, g.name, a.employee_id, c.schedule_date";

	$ci =& get_instance();

	return $ci->db->query($sql);
}

function get_employee_dtr_from($employee_id, $date)
{
	$ci =& get_instance();

	$ci->db->where('employee_id', $employee_id);	
	$ci->db->where('date', $date);	
	$ci->db->where('deleted', 0);

	$result = $ci->db->get('employee_dtr');

	return $result;
}

function get_form($employee_id, $type, $period = null, $date, $approved = true, $late_file = false, $strict_no_late_file = false)
{
	$ci =& get_instance();	
	
	$ci->db->where('employee_id', $employee_id);

	if(  $strict_no_late_file && !is_null($period) ){
		$ci->db->where('(DATE_FORMAT(date_approved, "%Y-%m-%d") <= \'' . $period->cutoff .'\')', '', false);
	}
	else if (!is_null($period) && $late_file ) {
		$ci->db->where('(DATE_FORMAT(date_approved, "%Y-%m-%d") <= \'' . $period->cutoff .'\')', '', false);
	}
	
	if ($approved) {
		$ci->db->where('form_status_id', 3);
	} else {
		$ci->db->where('(form_status_id = 3 OR form_status_id = 2 OR form_status_id = 4)');
	}	

	switch ($type) {
		case 'oot':
		case 'ds':
		case 'out':
		case 'dtrp':
			$ci->db->where('date', $date);
			$ci->db->where('deleted', 0);
			break;
		case 'cws':
			$ci->db->join('employee_cws_dates', 'employee_cws_dates.employee_cws_id = employee_cws.employee_cws_id', 'left');
			$ci->db->where('employee_cws_dates.date', $date);
			$ci->db->where('deleted', 0);
			break;
		case 'obt':
			$ci->db->join('employee_obt_date', 'employee_obt_date.employee_obt_id = employee_obt.employee_obt_id', 'left');
			$ci->db->where('\'' . $date . '\' =  '. $ci->db->dbprefix . 'employee_obt_date.date', null, false);
			$ci->db->where('employee_obt_date.deleted = 0', null, false);
			$ci->db->where('employee_obt.deleted = 0', null, false);
			break;
		case 'et':
			$ci->db->where('datelate', $date);
			$ci->db->where('deleted', 0);
			break;
		default:
			$ci->db->where('date_from >=', $date);
			$ci->db->where('date_from <=', $date);
			$ci->db->where('deleted', 0);
			break;
	}
/*	if ($date == '2018-01-26' && $type == 'oot'){
		dbug($ci->db->last_query());
		die();
	}*/

	return $ci->db->get('employee_' . $type);
}

function get_late_file_form($employee_id, $type, $period = null, $date, $approved = true)
{
	return get_form($employee_id, $type, $period, $date, $approved, true);
}

function get_form_approved_processed($employee_id, $type, $period = null)
{
	$ci =& get_instance();	

	$ci->db->where('employee_id', $employee_id);

	if (!is_null($period)) {		
		$ci->db->where('date_approved <=', $period->date_to);
		$ci->db->where('date_approved >=', $period->date_from);
	}
	
	$ci->db->where('form_status_id', 3);
	$ci->db->where('processd', 0);
	$ci->db->where('deleted', 0);	

	switch ($type) {
		case 'oot':			
			break;
		case 'out':
		case 'dtrp':			
			break;
		case 'cws':
			break;
		case 'obt':
			$ci->db->where('(\'' . $date . '\' BETWEEN date_from AND date_to)', null, false);
			break;
		default:
			break;
	}

	return $ci->db->get('employee_' . $type);
}

function get_form_by_id_type($record_id, $type)
{
	$ci =& get_instance();	

	if ($type != 'leave') {		
		$ci->db->where('employee_' . $type . '_id', $record_id);
		$ci->db->where('employee_' . $type . '.deleted', 0);	
		$table = 'employee_' . $type;
	} else {
		$table = 'employee_leaves';	
	}

	$ci->db->join('form_status', 'form_status.form_status_id = ' . $table . '.form_status_id', 'left');
	
	switch ($type) {
		case 'et':
			$form = $ci->db->get($table)->row();			
			$html = '<h4>Excused Tardiness</h4>';
			$html .= '<table width="100%">';
			$html .= '<tr><td>Date Late</td><td>'
			 . date($ci->config->item('display_date_format'), strtotime($form->datelate))
			 . '</td></tr>
			 <tr><td>Reason</td><td>' . $form->reason . '</td></tr>
			 <tr><td>Status</td><td>'. $form->form_status .'</td></tr>';

			$html .= '</table>';
			break;		
		case 'leave':
			$ci->db->select('employee_form_type.application_form, employee_leaves.*, form_status');
			$ci->db->where('employee_leave_id', $record_id);
			$ci->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_leaves.application_form_id', 'left');			
			$form = $ci->db->get($table)->row();

			$title_form = $form->application_form;

			if ($form->blanket_id != NULL){
				$title_form = 'Emergency Leave Blanket';
			}

			$html = '<h4>'. $title_form . '</h4>';
			$html .= '<table width="100%">';
			$html .= '<tr><td>Inclusive Dates</td><td>';

			$ci->db->where('employee_leave_id', $record_id);
			//$ci->db->where('deleted', 0);
			$ci->db->order_by('date', 'ASC');
			$dates_affected = $ci->db->get('employee_leaves_dates');

			foreach ($dates_affected->result() as $date) {
				$html .= date($ci->config->item('display_date_format'), strtotime($date->date)) . ' - ';
				$dur = $ci->db->get_where('employee_leaves_duration', array('duration_id' => $date->duration_id))->row();
				$html .= $dur->duration;
				if($date->cancelled == 1) {
					$html .= '&nbsp;<span class="red">Cancelled</span>&nbsp<span class="blue" title="'.$date->remarks.'"><i>Date : '.date("m/d/y",strtotime($date->date_cancelled)).'</i></span>';
				}
				$html .= '<br />';				
			}

			$html .= '</td></tr><tr><td>Reason</td><td>' . $form->reason . '</td></tr>
			 <tr><td>Status</td><td>'. $form->form_status .'</td></tr>';

			$html .= '</table>';
			break;
		case 'oot':								
			$form = $ci->db->get($table)->row();			
			$html = '<h4>Official Overtime</h4>';
			$html .= '<table width="100%">';
			$html .= '<tr><td>Inclusive Date and Time</td><td>' 
			 . date($ci->config->item('display_datetime_format'), strtotime($form->datetime_from)) . ' - '
			 . date($ci->config->item('display_datetime_format'), strtotime($form->datetime_to))
			 . '</td></tr>
			 <tr><td>Reason</td><td>' . $form->reason . '</td></tr>
			 <tr><td>Status</td><td>'. $form->form_status .'</td></tr>';						 

			$html .= '</table>';
			break;
		case 'out':
			$form = $ci->db->get($table)->row();			
			$html = '<h4>Official Undertime</h4>';
			$html .= '<table width="100%">';
			$html .= '<tr><td>Inclusive Date and Time</td>
			 <tr><td>' 
			 . date($ci->config->item('display_datetime_format'), strtotime($form->date . ' ' . $form->time_start)) . ' - '
			 . date($ci->config->item('display_datetime_format'), strtotime($form->date . ' ' . $form->time_end))
			 . '</td></tr>
			 <tr><td>Reason</td><td>' . $form->reason . '</td></tr>
			 <tr><td>Status</td><td>'. $form->form_status .'</td></tr>';				 

			$html .= '</table>';
			break;			
		case 'dtrp':
			$form = $ci->db->get($table)->row();

			$html = '<h4>Daily Time Record Problem</h4>';
			$html .= '<table width="100%">';
			$html .= '<tr><td>Date</td>
			 <td>' . date($ci->config->item('display_date_format'), strtotime($form->date)). '</td>
			 </tr>
				<tr><td>Time</td>
				<td>' 
				. date($ci->config->item('edit_datetime_format'), strtotime($form->time))
				. '</td>
				</tr>			 
			 <tr><td>Reason</td><td>' . $form->reason . '</td></tr>
			 <tr><td>Status</td><td>'. $form->form_status .'</td></tr>';				 

			$html .= '</table>';			
			break;
		case 'cws':
			if (CLIENT_DIR == "oams"){

				$ci->db->select('employee_cws.*, n.shift new_shift, c.shift_calendar curr_shift, form_status.form_status');
				
				$ci->db->join('timekeeping_shift_calendar c', 'c.shift_calendar_id = employee_cws.current_shift_calendar_id', 'left');
				$ci->db->join('timekeeping_shift n', 'n.shift_id = employee_cws.shift_id', 'left');
				$form = $ci->db->get($table)->row();


/*				$ci->db->select('shift_calendar new_shift');
				$ci->db->where('shift_calendar_id', $form->shift_id );
				$new_form = $ci->db->get('timekeeping_shift_calendar')->row();*/

				$html = '<h4>Change Work Schedule (CWS)</h4>';
				$html .= '<table width="100%">';
				$html .= '<tr><td>Date</td>
				 <td>' 
				 . date($ci->config->item('display_date_format'), strtotime($form->date_from)) 
				 . ' - ' . date($ci->config->item('display_date_format'), strtotime($form->date_to)) 
				 . '</td>
				 </tr>
				 <tr><td>Reason</td><td>' . $form->reason . '</td></tr>
				 <tr><td>Current Work Schedule</td><td>' . $form->curr_shift . '</td></tr>
				 <tr><td>New Schedule</td><td>' . $form->new_shift . '</td></tr>
				 <tr><td>Status</td><td>'. $form->form_status .'</td></tr>';			 

				$html .= '</table>';
			}	
			else{

				$ci->db->select('employee_cws.*, n.shift_calendar new_shift, c.shift_calendar curr_shift, form_status.form_status');
				$ci->db->join('timekeeping_shift_calendar c', 'c.shift_calendar_id = employee_cws.current_shift_calendar_id', 'left');
				$ci->db->join('timekeeping_shift_calendar n', 'n.shift_calendar_id = employee_cws.shift_calendar_id', 'left');
				$form = $ci->db->get($table)->row();


				$ci->db->select('shift new_shift');
				$ci->db->where('shift_id', $form->shift_id );
				$new_form = $ci->db->get('timekeeping_shift')->row();

				$html = '<h4>Change Work Schedule (CWS)</h4>';
				$html .= '<table width="100%">';
				$html .= '<tr><td>Date</td>
				 <td>' 
				 . date($ci->config->item('display_date_format'), strtotime($form->date_from)) 
				 . ' - ' . date($ci->config->item('display_date_format'), strtotime($form->date_to)) 
				 . '</td>
				 </tr>
				 <tr><td>Reason</td><td>' . $form->reason . '</td></tr>
				 <tr><td>Current Work Schedule</td><td>' . $form->curr_shift . '</td></tr>
				 <tr><td>New Schedule</td><td>' . ($form->shift_id != 0 ? $new_form->new_shift : 'Rest Day'). '</td></tr>
				 <tr><td>Status</td><td>'. $form->form_status .'</td></tr>';			 

				$html .= '</table>';

			}		
			break;
		case 'obt':
			$form = $ci->db->get($table)->row();

			$html = '<h4>Official Business Trip</h4>';
			// $html .= '<table width="100%">';
			// $html .= '
			// 	<tr><td>Company</td><td>' . $form->company_to_visit . '</td></tr>	
			// 	<tr><td>Location</td><td>' . $form->company_location . '</td></tr>
			// 	<tr><td>Contact</td><td>' . $form->contact_name . '</td></tr>
			// 	<tr><td>Position</td><td>' . $form->contact_position . '</td></tr>
			// 	<tr><td>Contact No.</td><td>' . $form->contact_number . '</td></tr>';

			// $html .= '</table>';
			$html .= '<table width="100%">';
			$html .= '<tr><td>Inclusive Dates</td><td>';

			$ci->db->where('employee_obt_id', $record_id);
			//$ci->db->where('deleted', 0);
			$ci->db->order_by('date', 'ASC');
			$dates_affected = $ci->db->get('employee_obt_date');

			foreach ($dates_affected->result() as $date) {
				$html .= date($ci->config->item('display_date_format'), strtotime($date->date)) . ' - '.date('h:i a',strtotime($date->time_start)).'&nbsp;&nbsp;to&nbsp;&nbsp;'.date('h:i a',strtotime($date->time_end));
				if($date->cancelled == 1) {
					$html .= '&nbsp&nbsp<span class="red">Cancelled</span>&nbsp<span class="blue" title="'.$date->remarks.'"><i>Date : '.date("m/d/y",strtotime($date->date_cancelled)).'</i></span>';
				}
				$html .= '<br />';				
			}

			$html .= '</td></tr><tr><td>Reason</td><td>' . $form->reason . '</td></tr>
			 <tr><td>Status</td><td>'. $form->form_status .'</td></tr>';

			$html .= '</table>';
			break;
		case 'ds':
			$form = $ci->db->get($table)->row();			
			$html = '<h4>Double Shift</h4>';
			$html .= '<table width="100%">';
			$html .= '<tr><td>Inclusive Date and Time</td><td>' 
			 . date($ci->config->item('display_datetime_format'), strtotime($form->datetime_from)) . ' - '
			 . date($ci->config->item('display_datetime_format'), strtotime($form->datetime_to))
			 . '</td></tr>
			 <tr><td>Status</td><td>'. $form->form_status .'</td></tr>';						 

			$html .= '</table>';
			break;
		default:
			break;
	}

	return $html;
}

// suspended employees
function get_employee_suspended($emp_id,$cdate)
{
	$ci=& get_instance();

	$ci->db->join('employee_da','employee_da.da_id = sanction_date.da_id');
	$ci->db->where('employee_da.employee_id', $emp_id);
	$ci->db->where('employee_da.deleted', 0);
	$ci->db->where('sanction_date.sanction_date', $cdate);
	$suspended = $ci->db->get('sanction_date');

	if($suspended->num_rows() > 0)
		return $suspended;
	else
		return false;
}

function process_time_raw($location_id,$user_id,$dates)
{
	$ci=& get_instance();
	$ci->db->order_by('checktime');
	$ci->db->where_in('date', $dates);
	$ci->db->where(array('location_id' => $location_id, 'processed' => 0));
	$dtr_raw = $ci->db->get('employee_dtr_raw' );
	
	if ($dtr_raw->num_rows() == 0) {
		return;
	}
	
	$o_dtr_raw = $dtr_raw->result();

	$e_dtr = array();	

	foreach ($o_dtr_raw as $dtr_entry) {				
		if (trim($dtr_entry->checktype) == 'C/In') {
			$e_dtr[$dtr_entry->employee_id][$dtr_entry->date]['in'][] = $dtr_entry->checktime;
		} else {
			$e_dtr[$dtr_entry->employee_id][$dtr_entry->date]['out'][] = $dtr_entry->checktime;
		}

		$o_dtr_raw_processed[] = $dtr_entry->log_id;
	}	

	foreach ($e_dtr as $employee_id => &$employee_day_record) {
		foreach ($employee_day_record as $date => &$e_dtr_entry) {		
			$next_day = date('Y-m-d', strtotime('+1 day', strtotime($date)));
			$prev_day = date('Y-m-d', strtotime('-1 day', strtotime($date)));

			if ( isset( $e_dtr_entry['in'][0] ) && $e_dtr_entry['out'][0] && strtotime($e_dtr_entry['in'][0]) > strtotime($e_dtr_entry['out'][0])) {
				unset($e_dtr_entry['out']);
			}

			// With IN / No out
			if (array_key_exists('in', $e_dtr_entry) && !array_key_exists('out', $e_dtr_entry)) {				
				// Get earliest out 
				if (!is_null($employee_day_record[$next_day]['out'])) {					
					/*$employee_day_record[$next_day]['out'] = array_values($employee_day_record[$next_day]['out']);*/
					foreach ($employee_day_record[$next_day]['out'] as $idx => $next_day_out) {
						if (strtotime($next_day_out) < strtotime($employee_day_record[$next_day]['in'][0])) {
							unset($employee_day_record[$next_day]['out'][0]);

							if (isset($employee_day_record[$next_day]['out'][$idx + 1])){
								$employee_day_record[$next_day]['out'][0] = $employee_day_record[$next_day]['out'][$idx + 1];
								unset($employee_day_record[$next_day]['out'][$idx + 1]);
							}

							$e_dtr_entry['out'][0] = $next_day_out;
							continue;
						}
						// Default to last out if no out is less than in
						//$e_dtr_entry['out'][0] = $next_day_out; // tirso - temporary comment since it will get next day out if it the real time out of next day schedule.
					}
				} else if (count($e_dtr_entry['in']) > 1) {// Get last 'in' of the day
					if (count($e_dtr_entry['in']) > 0 && !isset($e_dtr_entry['in'][0])) {
						$e_dtr_entry['in'] = array_values($e_dtr_entry['in']);
					}

					$e_dtr_entry['out'][0] = end($e_dtr_entry['in']);
					unset($e_dtr_entry['in'][count($e_dtr_entry['in']) - 1]);
				} else if ($e_dtr_entry['in'][0] != null) { // Still no out check next day records	
								
					//this is for the next day grab to the prev. day for out
					// $e_dtr_entry['out'][0] = $employee_day_record[$next_day]['in'][0];
					// unset($employee_day_record[$next_day]['in'][0]);

				}			
			} else {
				// Check whether in/out on this day is correct (overlapping days)
				// Get next day outs to determine if out was there.
				if( isset( $employee_day_record[$next_day]['out'] ) ){
					foreach ($employee_day_record[$next_day]['out'] as $next_day_out) {
						if (strtotime($next_day_out) < strtotime($employee_day_record[$next_day]['in'][0])) {
							$e_dtr_entry['out'][0] = $next_day_out;
							continue;
						}
					}
				}

				// Default to last out if no out is less than in
				// $e_dtr_entry['out'][0] = $next_day_out;
			}

			$ci->db->where('employee_id', $employee_id);
			$ci->db->where('date', $date);
			$ci->db->where('deleted', 0);

			if(sizeof($e_dtr_entry['out']) > 1){
				//get the last out from the array
				$e_dtr_entry['out'][0] = $e_dtr_entry['out'][  sizeof($e_dtr_entry['out']) - 1];
			}
			
			$result = $ci->db->get('employee_dtr');
			$employee_dtr_row = array(
				'location_id' => $location_id,
				'date' => $date,
				'upload_by' => $user_id	
			);
			
			if( isset($e_dtr_entry['in'][0]) ) $employee_dtr_row['time_in1'] = $e_dtr_entry['in'][0];
			if( isset($e_dtr_entry['out'][0]) ) $employee_dtr_row['time_out1'] = $e_dtr_entry['out'][0];

			if ($result->num_rows() > 0) {
				$entry = $result->row();

				if( !isset( $e_dtr_entry['in'][0] ) || ( isset( $e_dtr_entry['in'][0] ) && $e_dtr_entry['in'][0] == "") ){
					$employee_dtr_row['time_in1'] = $entry->time_in1;
				}

				if( !isset( $e_dtr_entry['out'][0] ) || ( isset( $e_dtr_entry['out'][0] ) && $e_dtr_entry['out'][0] == "") ){
					$employee_dtr_row['time_out1'] = $entry->time_out1;
				}

				if( !empty($employee_dtr_row['time_in1']) && !empty($entry->time_in1) ){
					if( strtotime($entry->time_in1) < strtotime($employee_dtr_row['time_in1']) ){
						$employee_dtr_row['time_in1'] = $entry->time_in1;
					}
				}

				if( !empty($employee_dtr_row['time_out1']) && !empty($entry->time_out1) ){
					if( strtotime($entry->time_out1) > strtotime($employee_dtr_row['time_out1']) ){
						$employee_dtr_row['time_in1'] = $entry->time_in1;
					}
				}

				if( $entry->processed == 0 ){
					$ci->db->where('id', $entry->id);
					$ci->db->update('employee_dtr', $employee_dtr_row);
				}

			} else {				
				$employee_dtr_row['employee_id'] = $employee_id;
				$ci->db->insert('employee_dtr', $employee_dtr_row);
			}
		}		
	}

	// Set logs to processed.
	$ci->db->query('UPDATE '.$ci->db->dbprefix('employee_dtr_raw').' SET processed = 1 WHERE log_id IN ('.implode(',',$o_dtr_raw_processed).') AND date < date_sub(curdate(),interval 2 day)');	
}

//asian shipping
function as_process_time_raw($location_id,$user_id)
{
	$ci=& get_instance();
	$ci->db->order_by('checktime');
	$dtr_raw = $ci->db->get_where('employee_dtr_raw', array('location_id' => $location_id, 'processed' => 0));
	
	if ($dtr_raw->num_rows() == 0) {
		return;
	}
	
	$o_dtr_raw = $dtr_raw->result();

	$e_dtr = array();	

	foreach ($o_dtr_raw as $dtr_entry) {				
		switch($dtr_entry->checktype){
			case 'C/In':
				$e_dtr[$dtr_entry->employee_id][$dtr_entry->date]['in'][] = $dtr_entry->checktime;
				break;
			case 'B/Out':
				$e_dtr[$dtr_entry->employee_id][$dtr_entry->date]['bout'][] = $dtr_entry->checktime;
				break;
			case 'B/In':
				$e_dtr[$dtr_entry->employee_id][$dtr_entry->date]['bin'][] = $dtr_entry->checktime;
				break;
			case 'C/Out':
				$e_dtr[$dtr_entry->employee_id][$dtr_entry->date]['out'][] = $dtr_entry->checktime;
				break;
		}
		
		$o_dtr_raw_processed[] = $dtr_entry->log_id;
	}	

	foreach ($e_dtr as $employee_id => &$employee_day_record) {
		foreach ($employee_day_record as $date => &$e_dtr_entry) {		
			$next_day = date('Y-m-d', strtotime('+1 day', strtotime($date)));
			$prev_day = date('Y-m-d', strtotime('-1 day', strtotime($date)));

			if (strtotime($e_dtr_entry['in'][0]) > strtotime($e_dtr_entry['out'][0])) {
				unset($e_dtr_entry['out']);
			}
			
			// With IN / No out
			if (array_key_exists('in', $e_dtr_entry) && !array_key_exists('out', $e_dtr_entry)) {				
				// Get earliest out 
				if (!is_null($employee_day_record[$next_day]['out'])) {					
					/*$employee_day_record[$next_day]['out'] = array_values($employee_day_record[$next_day]['out']);*/
					foreach ($employee_day_record[$next_day]['out'] as $next_day_out) {
						if (strtotime($next_day_out) < strtotime($employee_day_record[$next_day]['in'][0])) {
							$e_dtr_entry['out'][0] = $next_day_out;
							continue;
						}
						// Default to last out if no out is less than in
						//$e_dtr_entry['out'][0] = $next_day_out; // tirso - temporary comment since it will get next day out if it the real time out of next day schedule.
					}
				} else if (count($e_dtr_entry['in']) > 1) {// Get last 'in' of the day
					if (count($e_dtr_entry['in']) > 0 && !isset($e_dtr_entry['in'][0])) {
						$e_dtr_entry['in'] = array_values($e_dtr_entry['in']);
					}

					$e_dtr_entry['out'][0] = end($e_dtr_entry['in']);
					unset($e_dtr_entry['in'][count($e_dtr_entry['in']) - 1]);
				} else if ($e_dtr_entry['in'][0] != null) { // Still no out check next day records	
								
					//this is for the next day grab to the prev. day for out
					// $e_dtr_entry['out'][0] = $employee_day_record[$next_day]['in'][0];
					// unset($employee_day_record[$next_day]['in'][0]);

				}			
			} else {
				// Check whether in/out on this day is correct (overlapping days)
				// Get next day outs to determine if out was there.
				foreach ($employee_day_record[$next_day]['out'] as $next_day_out) {
					if (strtotime($next_day_out) < strtotime($employee_day_record[$next_day]['in'][0])) {
						$e_dtr_entry['out'][0] = $next_day_out;
						continue;
					}
				}

				// Default to last out if no out is less than in
				// $e_dtr_entry['out'][0] = $next_day_out;
			}

			$ci->db->where('employee_id', $employee_id);
			$ci->db->where('date', $date);
			$ci->db->where('deleted', 0);

			if(sizeof($e_dtr_entry['out']) > 1){
				//get the last out from the array
				$e_dtr_entry['out'][0] = $e_dtr_entry['out'][  sizeof($e_dtr_entry['out']) - 1];
			}

			$result = $ci->db->get('employee_dtr');
				$employee_dtr_row = array(
				'location_id' => $location_id,
				'date' => $date,
				'time_in1' => $e_dtr_entry['in'][0],
				'time_out1' => $e_dtr_entry['out'][0],
				'time_in2' => $e_dtr_entry['bin'][0],
				'time_out2' => $e_dtr_entry['bout'][0],
				'upload_by' => $user_id	
				);
			if ($result->num_rows() > 0) {
				$entry = $result->row();
				$ci->db->where('id', $entry->id);
				$ci->db->update('employee_dtr', $employee_dtr_row);
			} else {				
				$employee_dtr_row['employee_id'] = $employee_id;
				$ci->db->insert('employee_dtr', $employee_dtr_row);
			}
		}		
	}

	// Set logs to processed.
	$ci->db->query('UPDATE '.$ci->db->dbprefix('employee_dtr_raw').' SET processed = 1 WHERE log_id IN ('.implode(',',$o_dtr_raw_processed).') AND date < date_sub(curdate(),interval 2 day)');	
}

function process_time_raw_oams($location_id,$user_id)
{
	$ci=& get_instance();

	//$ci->db->query('SELECT date FROM '.$ci->db->dbprefix('employee_dtr_raw').' ');
	$ci->db->select_min('date');
	$ci->db->where('processed',0);
	$query = $ci->db->get('employee_dtr_raw');	

	$mindate = $query->row()->date;
	$lessdate = date('Y-m-d',strtotime('-1 day', strtotime($mindate)));

	$ci->db->where(array('location_id' => $location_id, 'processed' => 0));
	$ci->db->or_where('date >=',$lessdate);	
	$dtr_raw = $ci->db->get('employee_dtr_raw');	

	//$dtr_raw = $ci->db->get_where('employee_dtr_raw', array('location_id' => $location_id, 'processed' => 0));

	if ($dtr_raw){
		if ($dtr_raw->num_rows() == 0) {
			return;
		}
	}
	else{
		return;
	}
	
	$o_dtr_raw = $dtr_raw->result();

	$e_dtr = array();	
	foreach ($o_dtr_raw as $dtr_entry) {		

		if (trim($dtr_entry->checktype) == "C/In") {
			if (count($e_dtr[$dtr_entry->employee_id][$dtr_entry->date]['in']) < 2){
				$e_dtr[$dtr_entry->employee_id][$dtr_entry->date]['in'][] = $dtr_entry->checktime;
			}
		} else if (trim($dtr_entry->checktype) == "C/Out") {
			$e_dtr[$dtr_entry->employee_id][$dtr_entry->date]['out'] = $dtr_entry->checktime;

			// I think we might need this -JR
			$e_dtr[$dtr_entry->employee_id][$dtr_entry->date]['other_out'][] = $dtr_entry->checktime;
		}

		$o_dtr_raw_processed[] = $dtr_entry->log_id;
	}	

	$check_previous_day_for_in = false;

	foreach ($e_dtr as $employee_id => &$employee_day_record) {
		foreach ($employee_day_record as $date => &$e_dtr_entry) {	
			$next_day = date('Y-m-d', strtotime('+1 day', strtotime($date)));
			$prev_day = date('Y-m-d', strtotime('-1 day', strtotime($date)));

			$is_flexi = $ci->hdicore->is_flexi($employee_id);

			// get shifts
			$shift_sched = $ci->system->get_employee_worksched_shift($employee_id,$date);
			$prev_shift_sched = $ci->system->get_employee_worksched_shift($employee_id,$prev_day);

			// added to avoid duplicate IN/OUT on special case
			$compare_in = "0";
			$compare_out = "0";

			$time_in = $e_dtr_entry['in'][0];
			$time_out = $e_dtr_entry['out'];

			$use_prev_day_flag = false;
			$do_not_use_prev_day = false;

			// for flexi employees
			if($is_flexi)
			{
				// IN / OUT
				if(array_key_exists('in', $e_dtr_entry) && array_key_exists('out', $e_dtr_entry)) {
					if($e_dtr_entry['in'][0] < $e_dtr_entry['out'])
					{
						$time_in = $e_dtr_entry['in'][0];
						$time_out = $e_dtr_entry['out'];
					}
				}

				// IN / NO OUT
				if(array_key_exists('in', $e_dtr_entry) && !array_key_exists('out', $e_dtr_entry))
				{
					if (!is_null($employee_day_record[$next_day]['out'])) {
						if(($employee_day_record[$next_day]['in'][0] > $employee_day_record[$next_day]['out']) || (is_null($employee_day_record[$next_day]['in'][0]))) {
							$time_in = $e_dtr_entry['in'][0];
							$time_out = $employee_day_record[$next_day]['out'];
							unset($employee_day_record[$next_day]['out']);
							unset($employee_day_record[$next_day]['other_out'][0]);
							unset($e_dtr_entry['in'][0]);
						} else if($employee_day_record[$next_day]['in'][0] > $employee_day_record[$next_day]['other_out'][0]) {
							$time_out = $employee_day_record[$next_day]['other_out'][0];
						} else {
							$time_out = "";
						}
					}	
				}

				// NO IN / NO OUT
				if (!array_key_exists('in', $e_dtr_entry) && !array_key_exists('out', $e_dtr_entry)) {				
					if (!is_null($employee_day_record[$next_day]['in']) && !is_null($employee_day_record[$next_day]['out'])) {
						if ($employee_day_record[$next_day]['in'][0] > $employee_day_record[$next_day]['out']) {
							$time_out = $employee_day_record[$next_day]['out'];
							unset($employee_day_record[$next_day]['out']);
						}
						elseif ($employee_day_record[$next_day]['in'][0] < $employee_day_record[$next_day]['out']) {
							$time_in = $employee_day_record[$next_day]['in'][0];
							$time_out = $employee_day_record[$next_day]['out'];
							unset($employee_day_record[$next_day]['in'][0]);
							unset($employee_day_record[$next_day]['out']);
							$do_not_use_prev_day = true;
						}										
					}
					if (is_null($employee_day_record[$next_day]['in']) && !is_null($employee_day_record[$next_day]['out'])) {
						$time_out = $employee_day_record[$next_day]['out'];
						unset($employee_day_record[$next_day]['out']);
					}					
				}	

				if($time_out && ($time_in == "" || is_null($time_in) || !$time_in))
				{
					if(!is_null($employee_day_record[$prev_day]['in']))
					{
						if(end($employee_day_record[$prev_day]['in']) > $employee_day_record[$prev_day]['out'])
						{
							$time_in = end($employee_day_record[$prev_day]['in']);
							if(!$do_not_use_prev_day)
								$use_prev_day_flag = true;
						}
					}
				}

				if(date('H:i:s', strtotime($time_in)) >= "00:00:00" && date('H:i:s', strtotime($time_in)) <= "05:00:00") {
					if(!$do_not_use_prev_day)
						$use_prev_day_flag = true;
				}

			} else {

				// With IN / No out
				if (array_key_exists('in', $e_dtr_entry) && !array_key_exists('out', $e_dtr_entry)) {								
					if (!is_null($employee_day_record[$next_day]['out'])) {																														
						//for normal schedule that render overtime until next day
						if (!is_null($employee_day_record[$next_day]['in']) && !is_null($employee_day_record[$next_day]['out'])){
							if ($employee_day_record[$next_day]['in'][0] > $employee_day_record[$next_day]['out']) {
								$time_out = $employee_day_record[$next_day]['out'];
								unset($employee_day_record[$next_day]['out']);						
							}
						}
						if (is_null($employee_day_record[$next_day]['in']) && !is_null($employee_day_record[$next_day]['out'])){
							$time_out = $employee_day_record[$next_day]['out'];
							unset($employee_day_record[$next_day]['out']);						
						}			

						if (!is_null($employee_day_record[$next_day]['in']) && !is_null($employee_day_record[$next_day]['out'])){
							foreach($employee_day_record[$next_day]['other_out'] as $out)
							{
								if ($employee_day_record[$next_day]['in'][0] > $out) {
									$time_out = $employee_day_record[$next_day]['other_out'][0];
									unset($employee_day_record[$next_day]['other_out'][0]);						
								}
							}
						}		
					}				
				}

				// Focus date will be previous day
				if($prev_shift_sched->shifttime_start >= "00:00:00" && $prev_shift_sched->shifttime_start <= "05:00:00")
				{
					// Do not use previous day. if in is already in previous day
					if($time_in >= date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($time_in)).' 00:00:00')) && $time_in <= date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($time_in)).' 05:00:00'))) 
					{
						$use_prev_day_flag = true;
					}


					// for special case. 
					// Scenario: there is two valid "IN" in one day. to correct this, we need to save the previous one now. and use the normal way saving for current
					if($shift_sched->shifttime_start == "00:00:00")
					{
						$check_previous_day_for_in = true;

						// save prev schedule. so we won't be confuse with it.
						$ci->db->where('employee_id', $employee_id);
						$ci->db->where('date', ($use_prev_day_flag ? $prev_day : $date));
						$ci->db->where('deleted', 0);

						$result = $ci->db->get('employee_dtr');

						if(is_null($time_in) && is_null($time_out)) {
							continue;
						} else {

							$employee_dtr_row = array(
									'location_id' => $location_id,
									'date' => ($use_prev_day_flag ? $prev_day : $date),
									'time_in1' => $time_in,
									'time_out1' => $time_out,
									'upload_by' => $user_id	
									);

							if ($result->num_rows() > 0) {
								$entry = $result->row();
								if ($entry->processed == 1){
									if ($entry->time_in1 != NULL){
										$employee_dtr_row['time_in1'] = $entry->time_in1;
									}
									if ($entry->time_out1 != NULL){
										$employee_dtr_row['time_out1'] = $entry->time_out1;
									}						
								}
								$ci->db->where('id', $entry->id);
								$ci->db->update('employee_dtr', $employee_dtr_row);
							} else {
								$employee_dtr_row['employee_id'] = $employee_id;
								$ci->db->insert('employee_dtr', $employee_dtr_row);
							}
							$compare_in = $time_in;
							$compare_out = $time_out;
						}
					}

					if ($shift_sched->shifttime_start >= "16:00:00" && $shift_sched->shifttime_start <= "23:00:00")
					{
						foreach($e_dtr_entry['in'] as $in)
						{
							if($in > $e_dtr_entry['out'][0])
							{
								$time_in1 = $in;
								$time_out1 = $employee_day_record[$next_day]['out'];
							}
						}

						if(trim($employee_day_record[$next_day]['out']) != "" || $employee_day_record[$next_day]['out'] != null)
						{
							unset($employee_day_record[$next_day]['out']);
							$ci->db->where('employee_id', $employee_id);
							$ci->db->where('date', $date);
							$ci->db->where('deleted', 0);

							$result = $ci->db->get('employee_dtr');

							$employee_dtr_row = array(
								'location_id' => $location_id,
								'date' => $date,
								'time_in1' => $time_in1,
								'time_out1' => $time_out1,
								'upload_by' => $user_id	
								);

							if ($result->num_rows() > 0) {
								$entry = $result->row();
								if ($entry->processed == 1){
									if ($entry->time_in1 != NULL){
										$employee_dtr_row['time_in1'] = $entry->time_in1;
									}
									if ($entry->time_out1 != NULL){
										$employee_dtr_row['time_out1'] = $entry->time_out1;
									}						
								}
								$ci->db->where('id', $entry->id);
								$ci->db->update('employee_dtr', $employee_dtr_row);
							} else {
								$employee_dtr_row['employee_id'] = $employee_id;
								$ci->db->insert('employee_dtr', $employee_dtr_row);
							}
						}

					}
				}

				if ($shift_sched->shifttime_start == "00:00:00") {
					// With IN and OUT
					if (array_key_exists('in', $e_dtr_entry) && array_key_exists('out', $e_dtr_entry)) {
						if (!is_null($employee_day_record[$next_day]['out'])) { // get out on next day for the current date used.
							$time_in = $e_dtr_entry['in'][0];
							$time_out = $employee_day_record[$next_day]['out'];
							unset($employee_day_record[$next_day]['out']);											
						}
						else{ // for undertime
							if ($e_dtr_entry['in'][0] < $e_dtr_entry['out']){
								$time_in = $e_dtr_entry['in'][0];
								$time_out = $e_dtr_entry['out'];	
							}
							else{
								$time_in = $e_dtr_entry['in'][0];
								$time_out = "";						
							}
						}					
					}

					// No IN and No OUT
					if (!array_key_exists('in', $e_dtr_entry) && !array_key_exists('out', $e_dtr_entry)) {				
						if (!is_null($employee_day_record[$next_day]['in']) && !is_null($employee_day_record[$next_day]['out'])) {
							if ($employee_day_record[$next_day]['in'][0] > $employee_day_record[$next_day]['out']) {
								$time_out = $employee_day_record[$next_day]['out'];
								unset($employee_day_record[$next_day]['out']);
							}								
							elseif ($employee_day_record[$next_day]['in'][0] < $employee_day_record[$next_day]['out']) {
								$time_in = $employee_day_record[$next_day]['in'][0];
								$time_out = $employee_day_record[$next_day]['out'];
								unset($employee_day_record[$next_day]['in'][0]);
								unset($employee_day_record[$next_day]['out']);
							}										
						}
						if (is_null($employee_day_record[$next_day]['in']) && !is_null($employee_day_record[$next_day]['out'])) {
							$time_out = $employee_day_record[$next_day]['out'];
							unset($employee_day_record[$next_day]['out']);
						}					
					}	

					// With IN and No OUT
					if (array_key_exists('in', $e_dtr_entry) && !array_key_exists('out', $e_dtr_entry)) {				
						if (!is_null($employee_day_record[$next_day]['out'])) {
							$time_out = $employee_day_record[$next_day]['out'];
							unset($employee_day_record[$next_day]['out']);
						}										
					}	

					// special case 12am.
					if($check_previous_day_for_in) {
						foreach($e_dtr_entry['in'] as $in)
						{
							if($in > $e_dtr_entry['out'][0])
								$time_in = $in;
						}
						$use_prev_day_flag = false;
						$check_previous_day_for_in = false;
					}

				}

				if(!$use_prev_day_flag)
				{
					if ($shift_sched->shifttime_start >= "16:00:00" && $shift_sched->shifttime_start <= "23:00:00"){
						// With IN and OUT
						if (array_key_exists('in', $e_dtr_entry) && array_key_exists('out', $e_dtr_entry)) {
							if (!is_null($employee_day_record[$next_day]['out'])) { // get out on next day for the current date used.						
								$time_in = $e_dtr_entry['in'][0];
								$time_out = $employee_day_record[$next_day]['out'];
								unset($employee_day_record[$next_day]['out']);											
							}
							else{ // for undertime
								if ($e_dtr_entry['in'][0] < $e_dtr_entry['out']){
									$time_in = $e_dtr_entry['in'][0];
									$time_out = $e_dtr_entry['out'];	
								}
								else{
									$time_in = $e_dtr_entry['in'][0];
									$time_out = "";						
								}
							}
						}

						// No IN and No OUT
						if (!array_key_exists('in', $e_dtr_entry) && !array_key_exists('out', $e_dtr_entry)) {
							if (!is_null($employee_day_record[$next_day]['in']) && !is_null($employee_day_record[$next_day]['out'])) {
								if ($employee_day_record[$next_day]['in'][0] > $employee_day_record[$next_day]['out']) {
									$time_out = $employee_day_record[$next_day]['out'];
									unset($employee_day_record[$next_day]['out']);
								}
								elseif ($employee_day_record[$next_day]['in'][0] < $employee_day_record[$next_day]['out']) {
									$time_in = $employee_day_record[$next_day]['in'][0];
									$time_out = $employee_day_record[$next_day]['out'];
									unset($employee_day_record[$next_day]['in'][0]);
									unset($employee_day_record[$next_day]['out']);
								}
							}
							if (is_null($employee_day_record[$next_day]['in']) && !is_null($employee_day_record[$next_day]['out'])) {
								$time_out = $employee_day_record[$next_day]['out'];
								unset($employee_day_record[$next_day]['out']);
							}
						}

						// With IN and No OUT
						if (array_key_exists('in', $e_dtr_entry) && !array_key_exists('out', $e_dtr_entry)) {				
							if (!is_null($employee_day_record[$next_day]['out'])) {
								$time_out = $employee_day_record[$next_day]['out'];
								unset($employee_day_record[$next_day]['out']);
							}							
						}							
					}
				}

			} // end of if not flexi

			$ci->db->where('employee_id', $employee_id);
			$ci->db->where('date', ($use_prev_day_flag ? $prev_day : $date));
			$ci->db->where('deleted', 0);

			$result = $ci->db->get('employee_dtr');

			if(is_null($time_in) && is_null($time_out))
				continue;
			else {
				if($compare_in != $time_in && $compare_out != $time_out)
				{
					$employee_dtr_row = array(
							'location_id' => $location_id,
							'date' => ($use_prev_day_flag ? $prev_day : $date),
							'time_in1' => $time_in,
							'time_out1' => $time_out,
							'upload_by' => $user_id	
							);
					if ($result->num_rows() > 0) {
						$entry = $result->row();
						if ($entry->processed == 1){
							if ($entry->time_in1 != NULL){
								$employee_dtr_row['time_in1'] = $entry->time_in1;
							}
							if ($entry->time_out1 != NULL){
								$employee_dtr_row['time_out1'] = $entry->time_out1;
							}						
						}
						$ci->db->where('id', $entry->id);
						$ci->db->update('employee_dtr', $employee_dtr_row);
					} else {
						$employee_dtr_row['employee_id'] = $employee_id;
						$ci->db->insert('employee_dtr', $employee_dtr_row);
					}
				}
			}
		}		
	}

	// Set logs to processed.
	$ci->db->where_in('log_id', $o_dtr_raw_processed);
	$ci->db->update('employee_dtr_raw', array('processed' => 1));
	//$ci->db->query('UPDATE '.$ci->db->dbprefix('employee_dtr_raw').' SET processed = 1 WHERE log_id IN ('.implode(',',$o_dtr_raw_processed).') AND date < date_sub(curdate(),interval 2 day)');	
}
//for flexi employee
function is_double_shift_used($employee_id = false, $cdate = false)
{
	if($employee_id && $cdate)
	{
		$ci=& get_instance();
		$result = $ci->db->get_where('employee_dtr', array('double_shift_is_used <>' => 0, 'employee_id' => $employee_id, 'date' => $cdate));
		if($result && $result->num_rows() > 0)
			return true;
		else
			return false;
	}
}

function process_time_raw_with_timeline($location_id, $user_id)
{
	$ci =& get_instance();

	// variable declaration
	$dtr_raw = array();
	$log_id = array();

	// Get raw dtr data
	$qry = "SELECT *
			FROM {$ci->db->dbprefix}employee_dtr_raw
			WHERE processed = 0
				AND location_id = {$location_id}
			    AND employee_id <> 0
			    AND `date` >= (SELECT
			                     DATE_SUB(MIN(`date`), INTERVAL 1 DAY)
			                   FROM {$ci->db->dbprefix}employee_dtr_raw
			                   WHERE processed = 0)
			ORDER BY employee_id, `date`, checktime DESC
			";

	$result = $ci->db->query($qry);

	if($result && $result->num_rows() == 0)
		return;

	// set-up array to be processed
	foreach ($result->result() as $dtr_raw_entry) {
		switch(trim($dtr_raw_entry->checktype))
		{
			case "C/In":
				$dtr_raw[$dtr_raw_entry->employee_id][$dtr_raw_entry->date]['in'][] = $dtr_raw_entry->checktime;
				break;
			case "C/Out":
				$dtr_raw[$dtr_raw_entry->employee_id][$dtr_raw_entry->date]['out'][] = $dtr_raw_entry->checktime;
				break;
			case "Out":
				$dtr_raw[$dtr_raw_entry->employee_id][$dtr_raw_entry->date]['break_out'][] = $dtr_raw_entry->checktime;
				break;
			case "Out Back":
				$dtr_raw[$dtr_raw_entry->employee_id][$dtr_raw_entry->date]['break_in'][] = $dtr_raw_entry->checktime;
				break;
		}

		$date_with_entry[] = $dtr_raw_entry->date;

		$log_id[] = $dtr_raw_entry->log_id;
	}

	// set-up dates
	sort($date_with_entry);
	$date = date('Y-m-d', strtotime('-1 day', strtotime($date_with_entry[0])));
	$max = end($date_with_entry);
	
	foreach ($dtr_raw as $employee_id => &$employee_day_record)
		process_raw_entries($date, $max, $employee_id, $employee_day_record);

	$ci->db->where_in('log_id', $log_id);
	$ci->db->delete('employee_dtr_raw');

}

function process_raw_entries($date, $max, $employee_id, $employee_day_record)
{
	$ci =& get_instance();	

	if($date <= $max) {

		// get shift
		$shift_sched = $ci->system->get_employee_worksched_shift($employee_id, $date);

		$is_flexi = $ci->hdicore->is_flexi($employee_id);

		$holiday = $ci->system->holiday_check($date, $employee_id, true);

		if($shift_sched->shift_id == 1 || $shift_sched->shift_id == 0 || $shift_sched->shift == "RESTDAY" || $holiday) {
			process_restday_raw($date, $employee_id, $employee_day_record, $shift_sched);
		} else {
			if($is_flexi)
				process_flexi_raw($date, $employee_id, $employee_day_record, $shift_sched);
			else
				process_normal_raw($date, $employee_id, $employee_day_record, $shift_sched);
		}

		process_raw_entries(date('Y-m-d', strtotime('+1 day', strtotime($date))), $max, $employee_id, $employee_day_record);

	} else {
		return;
	}
}

function process_normal_raw($date, $employee_id, $employee_day_record, $shift_sched = false)
{
	if(!$shift_sched)
		return;

	$ci =& get_instance();

	// set-up header
	$header =  array( 'in' => 
						array( 
							'next_day', 
							'date', 
							'prev_day'
						),
					  'out' => 
					 	array( 'date', 
					 		   'next_day'
					 	)
					);

	// get / refresh variable
	$time_in = null;
	$time_out = null;
	$next_day = date('Y-m-d', strtotime('+1 day', strtotime($date)));
	$prev_day = date('Y-m-d', strtotime('-1 day', strtotime($date)));


	// get shift start and end with date
	if($shift_sched->shifttime_start >= "00:00:00" && $shift_sched->shifttime_start <= "14:00:00" && $shift_sched->shifttime_end >= "00:00:00" && $shift_sched->shifttime_end <= "14:00:00") {

		$shift_start = date('Y-m-d H:i:s', strtotime($next_day.' '.$shift_sched->shifttime_start));
		$shift_end = date('Y-m-d H:i:s', strtotime($next_day.' '.$shift_sched->shifttime_end));

	} else {

		$shift_start = date('Y-m-d H:i:s', strtotime($date.' '.$shift_sched->shifttime_start));

		if($shift_sched->shifttime_start > $shift_sched->shifttime_end) // to correct overlapping end shift
			$shift_end = date('Y-m-d H:i:s', strtotime($next_day.' '.$shift_sched->shifttime_end));
		else // get normal end shift
			$shift_end = date('Y-m-d H:i:s', strtotime($date.' '.$shift_sched->shifttime_end));

	}

	// get timeline
	$start_timeline = date('Y-m-d H:i:s', strtotime('- '.round($shift_sched->max_preshift_ot).' hours', strtotime($shift_start)));
	$end_timeline = date('Y-m-d H:i:s', strtotime('+ '.round($shift_sched->max_postshift_ot).' hours', strtotime($shift_end)));

	sort($employee_day_record[$date]['out']);
	sort($employee_day_record[$next_day]['out']);
	sort($employee_day_record[$prev_day]['out']);

	foreach($header as $status => $d_types)
	{
		foreach($d_types as $d_type)
		{
			foreach($employee_day_record[${$d_type}][$status] as $data)
			{
				if($status == 'in') {
					if($data >= $start_timeline && $data <= $shift_end)
						${'time_'.$status} = $data;
				} else if($status == 'out') {
					if($data <= $end_timeline && $data > $shift_start)
						${'time_'.$status} = $data;
				}
			}
		}
	}

	if(!is_null($time_in) || !is_null($time_out)) :

		$current_entry = $ci->db->get_where('employee_dtr', array('employee_id' => $employee_id, 'date' => $date, 'deleted' => 0));

		$processed_dtr_entry = array( 'date' => $date,
									  'location_id' => $location_id,
									  'upload_by' => $user_id
									  );

		if(!is_null($time_in))
			$processed_dtr_entry['time_in1'] = $time_in;

		if(!is_null($time_out))
			$processed_dtr_entry['time_out1'] = $time_out;

		if ($current_entry->num_rows() > 0) {
			$entry = $current_entry->row();
			if ($entry->processed == 1){
				if ($entry->time_in1 != NULL){
					$employee_dtr_row['time_in1'] = $entry->time_in1;
				}
				if ($entry->time_out1 != NULL){
					$employee_dtr_row['time_out1'] = $entry->time_out1;
				}						
			}
			$ci->db->where('id', $entry->id);
			$ci->db->update('employee_dtr', $processed_dtr_entry);
		} else {
			$processed_dtr_entry['employee_id'] = $employee_id;
			$ci->db->insert('employee_dtr', $processed_dtr_entry);
		}

	endif;

}

function process_restday_raw($date, $employee_id, $employee_day_record)
{
	$ci =& get_instance();

	$n_day = date('Y-m-d', strtotime('+1 day', strtotime($date)));
	$p_day = date('Y-m-d', strtotime('-1 day', strtotime($date)));
	$time_in = null;
	$time_out = null;
	$shift_date = null;
	// $check_tomorrow = false;

	$prev_shift = $ci->system->get_employee_worksched_shift($employee_id, $p_day);

	if($prev_shift->shifttime_start >= "00:00:00" && $prev_shift->shifttime_start <= "14:00:00" && $prev_shift->shifttime_end >= "00:00:00" && $prev_shift->shifttime_end <= "14:00:00")
	{

		foreach($employee_day_record[$date]['in'] as $in)
		{
			if($in >= end($employee_day_record[$date]['out']))
				$time_in = $in;
		}

		if(is_null($employee_day_record[$n_day]['in'])) {
			$time_out = end($employee_day_record[$n_day]['out']);
		} else {
			foreach($employee_day_record[$n_day]['out'] as $out) {
				if($employee_day_record[$n_day]['in'][0] >= $out)
					$time_out = $out;
			}
		}

	} else {

		if(!is_null($employee_day_record[$date]['in'])) {
			
			rsort($employee_day_record[$date]['in']);

			// look for in that is higher than shift-end
			foreach($employee_day_record[$date]['in'] as $in) {
				if($prev_shift->shifttime_start > $prev_shift->shifttime_end)
					$shift_date = $date;
				else
					$shift_date = $p_day;

				if(date('Y-m-d H:i:s', strtotime($shift_date.' '.$prev_shift->shifttime_end)) < date('Y-m-d H:i:s', strtotime($in)))
					$time_in = $in;
			}

			if(!is_null($employee_day_record[$date]['out'])) {
				if(!is_null($time_in)) {
					foreach($employee_day_record[$date]['out'] as $out) {
						if($time_in < $out) 
							$time_out = $out;
					}

					if(is_null($time_out)) {
						if(!is_null($employee_day_record[$n_day]['out']))
						{
							if(!is_null($employee_day_record[$n_day]['in'][0]))
							{
								foreach($employee_day_record[$n_day]['out'] as $out) {
									if($employee_day_record[$n_day]['in'][0] <= $out)
										$time_out = $out;
								}
							} else {
								$time_out = end($employee_day_record[$n_day]['out']);
							}
						}
					}

				}
			} else {
				if(!is_null($employee_day_record[$n_day]['out']))
				{
					if(!is_null($employee_day_record[$n_day]['in'][0]))
					{
						foreach($employee_day_record[$n_day]['out'] as $out) {
							if($employee_day_record[$n_day]['in'][0] <= $out)
								$time_out = $out;
						}
					} else {
						$time_out = end($employee_day_record[$n_day]['out']);
					}
				}
			}
		}

	}

	if(!is_null($time_in) || !is_null($time_out)) :

		$current_entry = $ci->db->get_where('employee_dtr', array('employee_id' => $employee_id, 'date' => $date, 'deleted' => 0));

		$processed_dtr_entry = array( 'date' => $date,
									  'location_id' => $location_id,
									  'upload_by' => $user_id
									  );

		if(!is_null($time_in))
			$processed_dtr_entry['time_in1'] = $time_in;

		if(!is_null($time_out))
			$processed_dtr_entry['time_out1'] = $time_out;

		if ($current_entry->num_rows() > 0) {
			$entry = $current_entry->row();
			if ($entry->processed == 1){
				if ($entry->time_in1 != NULL){
					$employee_dtr_row['time_in1'] = $entry->time_in1;
				}
				if ($entry->time_out1 != NULL){
					$employee_dtr_row['time_out1'] = $entry->time_out1;
				}						
			}
			$ci->db->where('id', $entry->id);
			$ci->db->update('employee_dtr', $processed_dtr_entry);
		} else {
			$processed_dtr_entry['employee_id'] = $employee_id;
			$ci->db->insert('employee_dtr', $processed_dtr_entry);
		}

		return;

	endif;
}

function process_flexi_raw($date, $employee_id, $employee_day_record)
{
	// [0] ung pinaka malaki
	// end ung pinakamaliit

	$ci =& get_instance();

	$use_prev_day_flag = false;

	$next_day = date('Y-m-d', strtotime('+1 day', strtotime($date)));
	$prev_day = date('Y-m-d', strtotime('-1 day', strtotime($date)));

	$use_prev_day_flag = false;
	$do_not_use_prev_day = false;

	sort($employee_day_record[$date]['out']);
	sort($employee_day_record[$next_day]['out']);

	// IN / OUT
	if(!is_null($employee_day_record[$date]['in']) && !is_null($employee_day_record[$date]['out'])) {
		if($employee_day_record[$date]['in'][0] < end($employee_day_record[$date]['out']))
		{
			$time_in = $employee_day_record[$date]['in'][0];
			$time_out = end($employee_day_record[$date]['out']);
		}
	}

	// IN / NO OUT
	if(!is_null($employee_day_record[$date]['in']) && (is_null($employee_day_record[$date]['out']) || $employee_day_record[$date]['in'][0] > end($employee_day_record[$date]['out'])))
	{
		if (!is_null($employee_day_record[$next_day]['out'])) {
			if(($employee_day_record[$next_day]['in'][0] > end($employee_day_record[$next_day]['out'])) || (is_null($employee_day_record[$next_day]['in']))) {
				$time_in = $employee_day_record[$date]['in'][0];
				$time_out = end($employee_day_record[$next_day]['out']);
			} else if($employee_day_record[$next_day]['in'][0] > $employee_day_record[$next_day]['out'][0]) {
				$time_in = end($employee_day_record[$date]['in']);
				$time_out = $employee_day_record[$next_day]['out'][0];
			} else {
				$time_out = "";
			}
		} else {
			$time_in = $employee_day_record[$date]['in'][0];
			$time_out = "";
		}

	}

	// NO IN / NO OUT
	if(is_null($employee_day_record[$date]['in']) && is_null($employee_day_record[$date]['out'])) {
		if (!is_null($employee_day_record[$next_day]['in']) && !is_null($employee_day_record[$next_day]['out'])) {

			if (end($employee_day_record[$next_day]['out']) < $employee_day_record[$next_day]['in'][0]) {
				$time_in = end($employee_day_record[$next_day]['in']);
				$time_out = end($employee_day_record[$next_day]['out']);
			}									
		}
		if (is_null($employee_day_record[$next_day]['in']) && !is_null($employee_day_record[$next_day]['out'])) {
			$time_out = $employee_day_record[$next_day]['out'];
		}					
	}	

	if(!is_null($time_in) || !is_null($time_out)) :

		$current_entry = $ci->db->get_where('employee_dtr', array('employee_id' => $employee_id, 'date' => ($use_prev_day_flag ? $prev_day : $date), 'deleted' => 0));

		$processed_dtr_entry = array( 'date' => ($use_prev_day_flag ? $prev_day : $date),
									  'location_id' => $location_id,
									  'upload_by' => $user_id
									  );

		if(!is_null($time_in))
			$processed_dtr_entry['time_in1'] = $time_in;

		if(!is_null($time_out))
			$processed_dtr_entry['time_out1'] = $time_out;

		if ($current_entry->num_rows() > 0) {
			$entry = $current_entry->row();
			if ($entry->processed == 1){
				if ($entry->time_in1 != NULL){
					$employee_dtr_row['time_in1'] = $entry->time_in1;
				}
				if ($entry->time_out1 != NULL){
					$employee_dtr_row['time_out1'] = $entry->time_out1;
				}						
			}
			$ci->db->where('id', $entry->id);
			$ci->db->update('employee_dtr', $processed_dtr_entry);
		} else {
			$processed_dtr_entry['employee_id'] = $employee_id;
			$ci->db->insert('employee_dtr', $processed_dtr_entry);
		}

		return;

	endif;
}

function process_time_biometrics($location_id, $user_id,$manual = false)
{
	$ci =& get_instance();

	// variable declaration
	$dtr_raw = array();
	$log_id = array();

	// Get raw dtr data
	$qry = "SELECT *
			FROM {$ci->db->dbprefix}employee_dtr_raw
			WHERE processed = 0
/*				AND location_id = {$location_id}*/
			    AND employee_id <> 0
			    -- AND DATE(`checktime`) >= (SELECT
			    --                  DATE_SUB(MIN(DATE(`checktime`)), INTERVAL 1 DAY)
			    --                FROM {$ci->db->dbprefix}employee_dtr_raw
			    --                WHERE processed = 0)			
			    AND employee_id = 520
			    /*AND `date` = '2015-09-14'*/
			ORDER BY employee_id, `date`, checktime ASC ";
	$result = $ci->db->query($qry);	
	if($result && $result->num_rows() == 0) {
		return;
	}
	foreach ($result->result() as $dtr_raw_entry) {

/*		if (!$manual){
			$ci->db->where('biometric_id', $dtr_raw_entry->employee_id);
		}
		else{
			$ci->db->where('employee_id', $dtr_raw_entry->employee_id);
		}*/

		$ci->db->where('employee_id', $dtr_raw_entry->employee_id);
		
		$ci->db->where('resigned', 0);
		$ci->db->where('deleted', 0);
		$ci->db->limit(1);

		$employee = $ci->db->get('employee');
// dbug($this->db->last_query());
		if ($employee && $employee->num_rows() > 0){
			$employee_id = $employee->row()->employee_id;

			$dtr_raw_id 	= $dtr_raw_entry->log_id;
			$cdate 			= date('Y-m-d',strtotime($dtr_raw_entry->checktime));
			$prevdate 		= date("Y-m-d",strtotime("-1days",strtotime($cdate)));	
			$date_entry 	= $dtr_raw_entry->checktime;
			$work_sched 	= $ci->system->get_employee_worksched($employee_id, $cdate);
			if(!empty($work_sched)) {
				$day = strtolower(date('l', $cdate));
				$day_shift_id = $work_sched->{$day . '_shift_id'};
				if(!empty($day_shift_id)) {
					$ci->db->where($ci->db->dbprefix('timekeeping_shift').'.shift_id',$day_shift_id);
					$result 	= $ci->db->get('timekeeping_shift');
					$workshift 	= $result->row();
					$shift_start 	= $workshift->shifttime_start;
					$shift_end 		= $workshift->shifttime_end;
					$noon_start 	= $workshift->noon_start;
					$noon_end 		= $workshift->noon_end;
					$focus_date 	= 1;
				} else {
					$shift_start 	= $work_sched->shifttime_start;
					$shift_end 		= $work_sched->shifttime_end;
					$noon_start 	= $work_sched->noon_start;
					$noon_end 		= $work_sched->noon_end;
					$focus_date 	= 1;
				}

				$shift_datetime_start_b	= date('Y-m-d H:i:s', strtotime('-8 hour',strtotime($cdate . ' ' . $shift_start)));
				$shift_datetime_start 	= date('Y-m-d H:i:s', strtotime($cdate . ' ' . $shift_start));
				$noon_datetime_start_bl	= date('Y-m-d H:i:s', strtotime('-30 min', strtotime($cdate . ' ' . $noon_start))); // buffer 30 min
				$noon_datetime_start 	= date('Y-m-d H:i:s', strtotime($cdate . ' ' . $noon_start));
				$noon_datetime_start_b 	= date('Y-m-d H:i:s', strtotime('+30 min', strtotime($cdate . ' ' . $noon_start))); // buffer 30 min
				$noon_datetime_end_bl	= date('Y-m-d H:i:s', strtotime('-30 min', strtotime($cdate . ' ' . $noon_end)));
				$noon_datetime_end 		= date('Y-m-d H:i:s', strtotime($cdate . ' ' . $noon_end));
				$noon_datetime_end_b	= date('Y-m-d H:i:s', strtotime('+30 min', strtotime($cdate . ' ' . $noon_end)));
				$shift_datetime_end 	= date('Y-m-d H:i:s', strtotime($cdate . ' ' . $shift_end));
				$shift_datetime_end_b 	= date('Y-m-d H:i:s', strtotime('+8 hour',strtotime($cdate . ' ' . $shift_end)));
				if (strtotime($shift_start) > strtotime($shift_end)) {
					$prev_shift_datetime_end_b 	= date('Y-m-d H:i:s', strtotime('+8 hour',strtotime($cdate . ' ' . $shift_end)));
				} else {
					$prev_shift_datetime_end_b 	= date('Y-m-d H:i:s', strtotime('+8 hour',strtotime($prevdate . ' ' . $shift_end)));
				}
				$cdate_in 	= $cdate;
				$cdate_bout = $cdate;
				$cdate_bin 	= $cdate;
				$cdate_out 	= $cdate; 

				if($focus_date == 1) { //focus date startdate					
					if($shift_start >= '06:00:00' && $shift_start <= '16:00:00') {				
						if(date('H:i:s', strtotime($date_entry)) >= '00:00:00' && date('H:i:s', strtotime($date_entry)) <= '06:00:00') {
							if (strtotime($shift_start) > strtotime($shift_end)) {
								$cdate = date('Y-m-d', strtotime('-1 day', strtotime($cdate)));
								$cdate_in 	= $cdate;
								$cdate_bout = $cdate;
								$cdate_bin 	= $cdate;
								$cdate_out 	= $cdate;
							}
						} else {
							if (strtotime($shift_start) > strtotime($shift_end)) {
								$shift_datetime_end = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $shift_end)));
								if ($noon_start >= '00:00:00' && $noon_start <= '06:00:00') {
									$noon_datetime_start = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $noon_start)));
								}
								if ($noon_end >= '00:00:00' && $noon_end <= '06:00:00') {
									$noon_datetime_end = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $noon_end)));		
								}
								$night_shift = true;
								$cdate = date('Y-m-d', strtotime('-1 day', strtotime($shift_datetime_end)));
								$cdate_in 	= $cdate;
								$cdate_bout = $cdate;
								$cdate_bin 	= $cdate;
								$cdate_out 	= $cdate;
							}
						}
					} else {
						if(date('H:i:s', strtotime($date_entry)) >= '00:00:00' && date('H:i:s', strtotime($date_entry)) <= '08:00:00') {
							$cdate = date('Y-m-d', strtotime('-1 day', strtotime($cdate)));
							$cdate_in 	= $cdate;
							$cdate_bout = $cdate;
							$cdate_bin 	= $cdate;
							$cdate_out 	= $cdate;
						} else {
							if (strtotime($shift_start) > strtotime($shift_end)) {
								$shift_datetime_end = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $shift_end)));
								if ($noon_start >= '00:00:00' && $noon_start <= '06:00:00') {
									$noon_datetime_start = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $noon_start)));
								}
								if ($noon_end >= '00:00:00' && $noon_end <= '06:00:00') {
									$noon_datetime_end = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $noon_end)));		
								}
								$night_shift = true;
								$cdate = date('Y-m-d', strtotime('-1 day', strtotime($shift_datetime_end)));
								$cdate_in 	= $cdate;
								$cdate_bout = $cdate;
								$cdate_bin 	= $cdate;
								$cdate_out 	= $cdate;
							}
						}
					}

					// to check previous date if with out or not
					$ci->db->where('employee_id', $employee_id);
					$ci->db->where(array('date' => $prevdate, 'deleted' => 0));
					$prev_edtr = $ci->db->get('employee_dtr');
					if ($prev_edtr && $prev_edtr->num_rows() > 0) { // if date line is not availble
						$prev_exist_date = $prev_edtr->row();
						if (!$prev_exist_date->time_out1 || $prev_exist_date->time_out1 == '' || (strtotime($prev_exist_date->time_out1) < strtotime($date_entry))){
							if ($date_entry <= $prev_shift_datetime_end_b){
								time_out1($prevdate,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
								//update_dtr_raw($dtr_raw_entry->log_id);
								continue;								
							}
						}
					}

					$ci->db->where('employee_id', $employee_id);
					$ci->db->where(array('date' => $cdate, 'deleted' => 0));
					$edtr = $ci->db->get('employee_dtr');
					if ($edtr && $edtr->num_rows() == 0) { // if date line is not availble
						$ci->db->insert('employee_dtr', array('time_in1' => $date_entry,'employee_id'=>$employee_id,'date'=>$cdate));
						update_dtr_raw($dtr_raw_entry->log_id);
					} else { // if date line is availble
						$exist_date = $edtr->row();
						
						if (($exist_date->time_in1 == null || $exist_date->time_in1 == '') && ($exist_date->time_in1 == null || $exist_date->time_in1 == '')){
							time_in1($cdate_in,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
						}
						else{
							if (!$exist_date->time_in1){
								if ($date_entry >= $shift_datetime_start_b && $date_entry <= $shift_datetime_end_b){
									time_in1($cdate_in,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
									//update_dtr_raw($dtr_raw_entry->log_id);
									continue;
								}
							}

							if($date_entry != $exist_date->time_in1 && $date_entry != $exist_date->time_out1 && $date_entry != $exist_date->time_in2 && $date_entry != $exist_date->time_out2) {						
								if($exist_date->time_in1 > $date_entry) {
									if ($date_entry >= $shift_datetime_start_b && $date_entry <= $shift_datetime_end_b){
										time_in1($cdate_in,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
										//update_dtr_raw($dtr_raw_entry->log_id);
										continue;
									}							
									time_in1($cdate_in,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
								}
								// dbug($cdate);	

								if(empty($exist_date->time_out1)) {
									if ($date_entry < $shift_datetime_end_b){
										time_out1($cdate_bout,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
									}
								}
								else{
									if ($exist_date->time_out1 < $date_entry){
										time_out1($cdate_bout,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
									}
								}

								if ($date_entry >= $noon_datetime_start && $date_entry <= $noon_datetime_end){
									if (empty($exist_date->time_out2)){
										time_out2($cdate_bout,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
										//update_dtr_raw($dtr_raw_entry->log_id);
										continue;	
									}
									else{
										if ($exist_date->time_out2 > $date_entry){
											time_out2($cdate_bout,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
											//update_dtr_raw($dtr_raw_entry->log_id);
											continue;	
										}
									}
								}

								if ($date_entry >= $noon_datetime_start && $date_entry <= $noon_datetime_end_b){
									if (!empty($exist_date->time_out2)){
										if (empty($exist_date->time_in2)){
											time_in2($cdate_bout,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
											//update_dtr_raw($dtr_raw_entry->log_id);
											continue;	
										}
										else{
											if ($date_entry < $noon_datetime_end){
												time_in2($cdate_bout,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
												//update_dtr_raw($dtr_raw_entry->log_id);
												continue;	
											}
											else{
												if ($exist_date->time_in2 < $noon_datetime_start || $exist_date->time_in2 > $noon_datetime_end){
													time_in2($cdate_bout,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
													//update_dtr_raw($dtr_raw_entry->log_id);
													continue;
												}
											}
										}
									}
								}
							}
						}
					}
				} else { //focus date enddate				
					if($shift_start >= '06:00:00' && $shift_start <= '16:00:00') {
						if(date('H:i:s', strtotime($date_entry)) >= '00:00:00' && date('H:i:s', strtotime($date_entry)) <= '06:00:00') {
							$cdate = $cdate;
							$cdate_in 	= $cdate;
							$cdate_bout = $cdate;
							$cdate_bin 	= $cdate;
							$cdate_out 	= $cdate;
						} else {
							if (strtotime($shift_start) > strtotime($shift_end)) {
								$shift_datetime_end = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $shift_end)));
								if ($noon_start >= '00:00:00' && $noon_start <= '06:00:00') {
									$noon_datetime_start = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $noon_start)));
								}
								if ($noon_end >= '00:00:00' && $noon_end <= '06:00:00') {
									$noon_datetime_end = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $noon_end)));		
								}
								$night_shift = true;
								$cdate = date('Y-m-d', strtotime($shift_datetime_end));
								$cdate_in 	= $cdate;
								$cdate_bout = $cdate;
								$cdate_bin 	= $cdate;
								$cdate_out 	= $cdate;
							}
						}
					} else {
						if(date('H:i:s', strtotime($date_entry)) >= '00:00:00' && date('H:i:s', strtotime($date_entry)) <= '08:00:00') {
							$cdate = $cdate;
							$cdate_in 	= $cdate;
							$cdate_bout = $cdate;
							$cdate_bin 	= $cdate;
							$cdate_out 	= $cdate;
						} else {
							if (strtotime($shift_start) > strtotime($shift_end)) {
								$shift_datetime_end = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $shift_end)));
								if ($noon_start >= '00:00:00' && $noon_start <= '06:00:00') {
									$noon_datetime_start = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $noon_start)));
								}
								if ($noon_end >= '00:00:00' && $noon_end <= '06:00:00') {
									$noon_datetime_end = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $noon_end)));		
								}
								$night_shift = true;
								$cdate = date('Y-m-d', strtotime($shift_datetime_end));
								$cdate_in 	= $cdate;
								$cdate_bout = $cdate;
								$cdate_bin 	= $cdate;
								$cdate_out 	= $cdate;
							}
						}
					}
					$ci->db->where('employee_id', $employee_id);
					$ci->db->where(array('date' => $cdate, 'deleted' => 0));
					$edtr = $ci->db->get('employee_dtr');
					if ($edtr && $edtr->num_rows() == 0) { // if date line is not availble
							$ci->db->insert('employee_dtr', array('time_in1' => $date_entry,'employee_id'=>$employee_id,'date'=>$cdate));
					} else { // if date line is availble

						$exist_date = $edtr->row();

						if (($exist_date->time_in1 == null || $exist_date->time_in1 == '') && ($exist_date->time_in1 == null || $exist_date->time_in1 == '')){
							time_in1($cdate_in,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
						}
						else{
							if($date_entry != $exist_date->time_in1 && $date_entry != $exist_date->time_out1 && $date_entry != $exist_date->time_in2 && $date_entry != $exist_date->time_out2) {						
								if($exist_date->time_in1 > $date_entry) {
									time_in1($cdate_in,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
									$date_entry = $exist_date->time_in1;
								}
								// dbug($cdate);							
								if(empty($exist_date->time_out1)) {	
									time_out1($cdate_out,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
								} else {
									if($exist_date->time_out1 < $date_entry) {
										time_out1($cdate_out,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
										$date_entry = $exist_date->time_out1;
									}
									if(empty($exist_date->time_in2)) {	
										time_in2($cdate_bin,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
									} else {
										// dbug($noon_datetime_start.' '.$date_entry.' '.date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($noon_datetime_start))));
										if($date_entry < $noon_datetime_start) {
											if($date_entry > date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($noon_datetime_start)))) {
												// dbug($noon_datetime_start.' '.$exist_date->time_in2.' '.date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($noon_datetime_start))));
												if($exist_date->time_in2 < $noon_datetime_start && $exist_date->time_in2 > date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($noon_datetime_start)))) {										
													if($exist_date->time_in2 > $date_entry) {
														time_in2($cdate_bin,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
														$date_entry = $exist_date->time_in2;
													}
												} else {
													if($exist_date->time_in2 < $date_entry) {
														time_in2($cdate_bin,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
														$date_entry = $exist_date->time_in2;
													}
												}
											}
										} else {
											if($exist_date->time_in2 < $noon_datetime_start && $exist_date->time_in2 > date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($noon_datetime_start)))) {
												if($exist_date->time_in2 > $date_entry) {
													$shift_time = array(1=>$exist_date->time_in2,2=>$date_entry);
													$focus_date_time = $noon_datetime_start;
													$close = get_close_date($shift_time, $focus_date_time);
													switch ($close['best']) {
														case 2:
															time_in2($cdate_bin,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
															$date_entry = $exist_date->time_in2;
															break;
													}
												}
											} else {
												if($exist_date->time_in2 < $date_entry) {
													$shift_time = array(1=>$exist_date->time_in2,2=>$date_entry);
													$focus_date_time = $noon_datetime_start;
													$close = get_close_date($shift_time, $focus_date_time);
													switch ($close['best']) {
														case 2:
															time_in2($cdate_bin,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
															$date_entry = $exist_date->time_in2;
															break;
													}
												}
											}
										}
										if(empty($exist_date->time_out2)) {	
											time_out2($cdate_bout,$date_entry,$employee_id,$exist_date);
										} else {
											if($date_entry > $noon_datetime_end) {
												if($date_entry < date('Y-m-d H:i:s', strtotime('+2 hour', strtotime($noon_datetime_end)))) {											
													if($exist_date->time_out2 > $noon_datetime_end && $exist_date->time_out2 < date('Y-m-d H:i:s', strtotime('+2 hour', strtotime($noon_datetime_end)))) {									
														
														if($exist_date->time_out2 < $date_entry) {
															time_out2($cdate_bout,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
														}
													} else {												
														if($exist_date->time_out2 < $date_entry) {													
															time_out2($cdate_bout,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
														}
													}
												}
											} else {
												if($exist_date->time_out2 > $noon_datetime_end && $exist_date->time_out2 < date('Y-m-d H:i:s', strtotime('+2 hour', strtotime($noon_datetime_end)))) {
													if($exist_date->time_out2 < $date_entry) {
														$shift_time = array(1=>$exist_date->time_out2,2=>$date_entry);
														$focus_date_time = $noon_datetime_end;
														$close = get_close_date($shift_time, $focus_date_time);
														switch ($close['best']) {
															case 2:
																time_out2($cdate_bout,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
																break;
														}
													}
												} else {
													if($exist_date->time_out2 < $date_entry) {
														$shift_time = array(1=>$exist_date->time_out2,2=>$date_entry);
														$focus_date_time = $noon_datetime_end;
														$close = get_close_date($shift_time, $focus_date_time);
														switch ($close['best']) {
															case 2:
																time_out2($cdate_bout,$date_entry,$employee_id,$exist_date,$dtr_raw_entry->log_id);
																break;
														}
													}
												}
											}
										}
									}
								}
							}							
						}
					}
				}

				$cdate_2 = date("Y-m-d",strtotime("-1days",strtotime($cdate)));		
				$cdate_2 = $cdate;		
				//$ci->db->update('employee_dtr_raw', array('processed' => 1),array('log_id'=>$dtr_raw_entry->log_id));
			} else {
				$cdate_2 = date("Y-m-d",strtotime("-1days",strtotime($cdate)));
				$cdate_2 = $cdate;		
				$ci->db->update('employee_dtr_raw', array('processed' => 2),array('date'=>$cdate_2,'employee_id'=>$employee_id));
			}			
		}
	}
	return;
}

function update_dtr_raw($log_id){
	$ci =& get_instance();
	$ci->db->update('employee_dtr_raw', array('processed' => 1),array('log_id'=>$log_id));
}

function time_in1($cdate,$date_entry,$employee_id,$exist_date,$log_id) {
	$ci =& get_instance();
	$ci->db->update('employee_dtr', array('time_in1' => $date_entry),array('date' => $cdate, 'employee_id'=>$employee_id, 'deleted' => 0));
	
	if ($ci->db->affected_rows() > 0){
		$ci->db->update('employee_dtr_raw', array('processed' => 1),array('log_id'=>$log_id));
	}
}

function time_out1($cdate,$date_entry,$employee_id,$exist_date,$log_id) {
	$ci =& get_instance();
	$ci->db->update('employee_dtr', array('time_out1' => $date_entry),array('date' => $cdate, 'employee_id'=>$employee_id, 'deleted' => 0));
	
	if ($ci->db->affected_rows() > 0){
		$ci->db->update('employee_dtr_raw', array('processed' => 1),array('log_id'=>$log_id));
	}
}

function time_in2($cdate,$date_entry,$employee_id,$exist_date,$log_id) {
	$ci =& get_instance();
	$ci->db->update('employee_dtr', array('time_in2' => $date_entry),array('date' => $cdate, 'employee_id'=>$employee_id, 'deleted' => 0));

	if ($ci->db->affected_rows() > 0){
		$ci->db->update('employee_dtr_raw', array('processed' => 1),array('log_id'=>$log_id));
	}
}

function time_out2($cdate,$date_entry,$employee_id,$exist_date,$log_id) {
	$ci =& get_instance();

	if ($ci->db->affected_rows() > 0){
		$ci->db->update('employee_dtr_raw', array('processed' => 1),array('log_id'=>$log_id));
	}
}

function get_close_date($shift_time, $focus_date_time) {
	$best = false;
	$diff = '';
	$bestDiff = 'X';
	for ($index = 1; $index <= count($shift_time); $index++) {
	    $diff = abs(strtotime($focus_date_time) - strtotime($shift_time[$index]));
	    if ($best === false || $diff < $bestDiff) {
	        $best = $index;
	        $alldaway = $shift_time[$index];
	        $bestDiff = $diff;
	    }
	}
	return array('best'=>$best,'alldaway'=>$alldaway,'bestDiff'=>$bestDiff);
}