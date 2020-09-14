<?php if (! defined('BASEPATH')) exit ('No direct script access allowed');

include (APPPATH . 'controllers/employee/dtr.php');

class Firstbalfour_dtr extends Dtr{
    
    // START custom module funtions
    function listview() {
        $this->load->helper('time_upload');

        // Set default filter       
        $filter['employee_id']  = $employee_id = ($this->input->post('employee_id') != '') ? $this->input->post('employee_id') : $this->userinfo['user_id'];
        $filter['date_from >='] = $date_from = ($this->input->post('date_from') != '') ? date('Y-m-d' , strtotime($this->input->post('date_from'))): date('Y-m-01');
        $filter['date_to <=']   = $date_to = ($this->input->post('date_to') != '') ? date('Y-m-d', strtotime($this->input->post('date_to'))) : date('Y-m-t');

        $response->msg_type = 'success';
        $response->msg = '';
        $response->page = 1;
        $response->records = 0;
        

        if (strtotime($date_to) < strtotime($date_from)) {
            $response->msg_type = 'attention';
            $response->msg = 'Invalid date range.';

            $this->load->view('template/ajax', array('json' => $response));
            return;
        }

        $cdate = $date_from;
        $cells = array();
        $cell_ctr = 0;  

        $records = 0;
        /*foreach ($a_schedule as $key => $shift_calendar) {                        */
        $workshift_day = array();
        $consecutive_absent = 0;
        $consecutive_absent_days = array();

        // Get employed date
        $this->db->where('employee_id', $employee_id);
        $this->db->where('deleted', 0);
        $result = $this->db->get('employee');

        $supervisor = false;
        $employed_date = '';

        if ($result){
            if ($result->num_rows() > 0){
                $employee = $result->row();

                if( in_array($employee->employee_type, $this->config->item('emp_type_no_late_ut')) ){
                    $supervisor = true;
                }               

                $employed_date = $employee->employed_date;              
            }
        }

        // Get dtr starting from starting date
        while (strtotime($cdate) <= strtotime($date_to)) {
            // check holiday.
            $holiday = $this->system->holiday_check($cdate, $employee_id);

            $forms = array();

            $w_tin = FALSE;
            $w_tout = FALSE;
            $rd = FALSE;
            $remarks = "";

            $dummy_p->date_to = $date_to;
            $dummy_p->date_from = $date_from;               

            // Check IN/OUT
            $id = 0;
            $dtr = get_employee_dtr_from($filter['employee_id'], $cdate);

            if ($dtr && $dtr->num_rows() > 0){
                $id = $dtr->row()->id;
            }
            //$cells[$cell_ctr]['id'] = $cell_ctr;
            $cells[$cell_ctr]['id'] = $id;
            $cell[0] = "<span style='float:left'>".display_date('D, M j Y', strtotime($cdate))."</span>";
            $cell[1] = ''; // IN
            $cell[2] = ''; // OUT             
            $cell[3] = ''; // shift           
            $cell[4] = ''; // Hours Worked  
            $cell[5] = '0.00'; // ET
            $cell[6] = '0.00'; // lates
            $cell[7] = '0.00'; // Autorized UT
            $cell[8] = '0.00'; // undertime
            $cell[9] = '0.00'; // overtime
            $cell[10] = '';


            //get schedule & shift
            $schedule = $this->system->get_employee_worksched($employee_id, $cdate, true);
            $shift_id = $schedule->shift_id;
            $shift = $schedule->shift;
            
            // Try to find CWS to use as shift for current date                 
            $cws = get_form($employee_id, 'cws', $dummy_p, $cdate, true);

            if ($cws->num_rows() > 0) {
                $cws = $cws->row();
                $shift_id = $schedule->shift_id;
                $shift = $schedule->shift;
                $forms['cws'] = $cws->employee_cws_id;
                $remarks = "cws";
                // back here
            }

            // to get for approval
            $cws_for_approval = get_form($employee_id, 'cws', $dummy_p, $cdate, false);
            if($cws_for_approval && $cws_for_approval->num_rows() > 0)
            {
                $cws_for_approval = $cws_for_approval->row();
                if($cws_for_approval->form_status_id == 2)
                {
                    $forms['cws'] = $cws_for_approval->employee_cws_id;
                    $remarks = "cws";
                }
            }

            if(! (strtotime(date('Y-m-d')) >= strtotime($cdate)) ) {
                $cell[1] = '';
            }

            if ($shift_id == 0 || $shift_id == 1) {// Rest day
                $cell[3] = 'Rest Day';
                //$w_tin = TRUE;
                //$w_tout = TRUE;
            } else if($this->hdicore->is_flexi($employee_id) && $this->config->item('with_flexi')) {
                $cell[3] = '<i>Flexible</i>';
                $cell[3] .= '<br /><small><i>'.$shift.'</i></small>';
            }else {
                $cell[3] = $shift;
            }           

            $dtr_in = '';
            $dtr_out = '';
            if ($dtr && $dtr->num_rows() > 0){
                $cell[4] = number_format($dtr->row()->hours_worked, 2);
                $cell[5] = number_format($dtr->row()->excused_tardiness / 60, 2);
                $cell[6] = number_format($dtr->row()->lates_display / 60, 2);
                $cell[7] = number_format($dtr->row()->approved_undertime / 60, 2);
                $cell[8] = number_format($dtr->row()->undertime_display / 60, 2);
                $cell[9] = number_format($dtr->row()->overtime / 60, 2);

                // $cell[3] = number_format($dtr->row()->hours_worked, 2);
                // $cell[5] = $dtr->row()->lates == 0 ? number_format($dtr->row()->lates / 60, 2) : floor((($dtr->row()->lates / 60)*100))/100;
                // $cell[7] = $dtr->row()->undertime == 0 ? number_format($dtr->row()->undertime / 60, 2) : floor((($dtr->row()->undertime / 60)*1000))/1000;
                // $cell[8] = $dtr->row()->overtime == 0 ? number_format($dtr->row()->overtime / 60, 2) : floor((($dtr->row()->overtime / 60)*100))/100;

                $dtr_in = $dtr->row()->time_in1;
                $dtr_out = $dtr->row()->time_out1;
            }

            if ( $dtr->num_rows() > 0 ){
                if ($dtr->row()->time_in1 != '' &&
                    !($dtr->row()->time_in1 == '0000-00-00 00:00:00' 
                        || $dtr->row()->time_in1 == '' || is_null($dtr->row()->time_in1))
                        ) {
                    $cell[1] = date('h:i:s a', strtotime($dtr->row()->time_in1));
                    $w_tin = TRUE;
                }

                if ($dtr->row()->time_out1 != '' &&
                    !($dtr->row()->time_out1 == '0000-00-00 00:00:00' 
                        || $dtr->row()->time_out1 == '' || is_null($dtr->row()->time_out1))
                        ) {
                    $cell[2] = date('h:i:s a', strtotime($dtr->row()->time_out1));
                    $w_tout = TRUE;
                }

                // Check OBT
                $obt = get_form($employee_id, 'obt', $dummy_p, $cdate, true);

                if ($obt->num_rows() > 0) {
                    $obts = $obt->result();

                    foreach($obts as $obt)
                    {
                        if ($dtr->row()->time_in1 == '0000-00-00 00:00:00' || $dtr->row()->time_in1 == '' 
                            || is_null($dtr->row()->time_in1)
                            || strtotime($obt->time_start) < strtotime(date('H:i:s', strtotime($dtr->row()->time_in1)))
                            ) {
                            $cell[1] = date('h:i:s a', strtotime($cdate . ' ' . $obt->time_start));
                            $dtr_in = date('Y-m-d H:i:s', strtotime($cdate . ' ' . $obt->time_start));
                            $w_tin = TRUE;
                        }

                        if ($dtr->row()->time_out1 == '0000-00-00 00:00:00' || $dtr->row()->time_out1 == '' 
                            || is_null($dtr->row()->time_out1) 
                            || strtotime($obt->time_end) > strtotime(date('H:i:s', strtotime($dtr->row()->time_out1)))
                            ) { 
                            $cell[2] = date('h:i:s a', strtotime($cdate . ' ' .$obt->time_end));
                            $dtr_out = date('Y-m-d H:i:s', strtotime($cdate . ' ' . $obt->time_end));
                            $w_tout = TRUE;
                        }

                        $forms['obt'][] = $obt->employee_obt_id;
                        $remarks = "obt";
                    }
                }                   

                //to get for approval
                $obt_for_approval = get_form($employee_id, 'obt', $dummy_p, $cdate, false);
                if($obt_for_approval && $obt_for_approval->num_rows() > 0)
                {
                    $obt_for_approval = $obt_for_approval->row();
                    if($obt_for_approval->form_status_id == 2)
                    {
                        $forms['obt'] = $obt_for_approval->employee_obt_id;
                        $remarks = "obt";
                    }
                }

                // Check leave for whole day
                $this->db->select('duration_id, employee_leaves.employee_leave_id, employee_leaves.form_status_id,employee_leaves_dates.cancelled');
                $this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
                $this->db->where('employee_id', $employee_id);
                $this->db->where('(\''. $cdate . '\' BETWEEN date_from and date_to)', '', false);
                $this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.date = \'' . $cdate . '\')', '',false);
                $this->db->where('(form_status_id = 3 OR form_status_id = 2 OR form_status_id = 4 )');
                $this->db->where('form_status_id <>', 1);
                //$this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.deleted = 0)', '', false);
                $this->db->where('employee_leaves.deleted', 0);

                $leave = $this->db->get('employee_leaves');

                if ($leave->num_rows() > 0) {         

                    if ($shift_id > 0 && $leave->row()->duration_id == 1 && $leave->row()->form_status_id == 3){ //if ($shift_id > 0) {                                                 
                        // if (strtolower($cell[1]) == 'no in' || strtolower($cell[1]) == 'absent') {
                            if($leave->row()->cancelled != '1') {
                                $cell[1] = 'LEAVE';
                                $w_tin = TRUE;
                            }
                        // }

                        // if (strtolower($cell[2]) == 'no out') {
                            if($leave->row()->cancelled != 1) {
                                $cell[2] = '';
                                $w_tout = TRUE;
                            }
                        // }
                    }

                    foreach ($leave->result() as $leave) {
                        $forms['leave'][] = $leave->employee_leave_id;
                    }
                }

            } else { // No time record entry

                if ($dtr && $dtr->num_rows() > 0){
                    $w_tin = is_valid_time($dtr->row()->time_in1);
                    $w_tout = is_valid_time($dtr->row()->time_out1);
                }
                // Check leave for whole day
                $this->db->select(' duration_id, employee_leaves.employee_leave_id, employee_leaves.form_status_id,employee_leaves.blanket_id,employee_leaves_dates.cancelled');
                $this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
                $this->db->where('employee_id', $employee_id);
                $this->db->where('(\''. $cdate . '\' BETWEEN date_from and date_to)', '', false);
                $this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.date = \'' . $cdate . '\')', '',false);
                $this->db->where('(form_status_id = 3 OR form_status_id = 2 OR form_status_id = 4)');
                $this->db->where('form_status_id <>', 1);
               // $this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.deleted = 0)', '', false);
                $this->db->where('employee_leaves.deleted', 0);

                $leave = $this->db->get('employee_leaves');
                
                if ($leave->num_rows() > 0) {                       
                    $w_tin = TRUE;
                    $w_tout = TRUE;
  
                    foreach ($leave->result() as $leave) {

                        $forms['leave'][] = $leave->employee_leave_id;

                        if ($leave->duration_id == 1 && $leave->form_status_id == 3 ) {
                            if($leave->cancelled != 1) {
                                $cell[1] = 'LEAVE';                         
                                $remarks = "";                      
                            }
                        }
                       
                        if ($shift_id > 0 && $leave->form_status_id == 3) {
                            if (strtolower($cell[1]) == 'no time in') {
                                if($leave->cancelled != 1) {
                                    $cell[1] = 'LEAVE';
                                }
                            }

                            if (strtolower($cell[2]) == 'no time out') {
                                $cell[2] = '';
                            }
                        }                           
                    }
                }


                // Check OBT
                $obt = get_form($employee_id, 'obt', $dummy_p, $cdate, true);

                if ($obt->num_rows() > 0) {
                    $obts = $obt->result();
                    foreach($obts as $obt)
                    {
                        if ($dtr->row()->time_in1 == '0000-00-00 00:00:00' || $dtr->row()->time_in1 == '' || is_null($dtr->row()->time_in1)) {
                            $cell[1] = date('h:i:s a', strtotime($cdate . ' ' . $obt->time_start));
                            $dtr_in = date('Y-m-d H:i:s', strtotime($cdate . ' ' . $obt->time_start));
                        } else {
                            $cell[1] = date('h:i:s a', strtotime($dtr->row()->time_in1));
                        }

                        if ($dtr->row()->time_out1 == '0000-00-00 00:00:00' || $dtr->row()->time_out1 == '' 
                            || is_null($dtr->row()->time_out1) 
                            || strtotime($obt->time_end) > strtotime(date('H:i:s', strtotime($dtr->row()->time_out1)))
                            ) { 
                            $cell[2] = date('h:i:s a', strtotime($cdate . ' ' .$obt->time_end));
                            $dtr_out = date('Y-m-d H:i:s', strtotime($cdate . ' ' . $obt->time_end));
                        } else {
                            $cell[2] = date('h:i:s a', strtotime($dtr->row()->time_out1));
                        }
                            
                        $w_tin = TRUE;
                        $w_tout = TRUE; 
                        $forms['obt'][] = $obt->employee_obt_id;
                    }
                }
            }

            //to get for approval
            $obt_for_approval = get_form($employee_id, 'obt', $dummy_p, $cdate, false);
            if($obt_for_approval && $obt_for_approval->num_rows() > 0)
            {
                $obt_for_approval = $obt_for_approval->row();
                if($obt_for_approval->form_status_id == 2)
                {
                    $forms['obt'] = $obt_for_approval->employee_obt_id;
                    $remarks = "obt";
                }
            }

            // Check other forms.
            $dtrp = get_form($employee_id, 'dtrp', $dummy_p, $cdate, false);
            if ($dtrp->num_rows() > 0) {
                foreach ($dtrp->result() as $_dtrp) {
                    if( $_dtrp->form_status_id == 3 ){
                        if ($_dtrp->time_set_id == 1) {
                            $cell[1] = date('h:i:s a', strtotime($_dtrp->time));
                            $w_tin = TRUE;
                            $dtr_in = $_dtrp->time;
                        } else {
                            $cell[2] = date('h:i:s a', strtotime($_dtrp->time));
                            $w_tout = TRUE;
                            $dtr_out = $_dtrp->time;
                        }
                    }

                    $forms['dtrp'][] = $_dtrp->employee_dtrp_id;
                }
            }           

/*            if ($schedule->considered_halfday){
                    $w_tin = TRUE;
                    $w_tout = TRUE;
                    $cell[9] = 'Considered Rest Day';
            }*/
            // Official overtime
            $oot = get_form($employee_id, 'oot', null, $cdate, false);

            if ($oot->num_rows() > 0) {
                foreach( $oot->result_array() as $oot_rec ){                        
                    $forms['oot'][] = $oot_rec['employee_oot_id'];                      
                }
            }

            // Official undertime
            $out = get_form($employee_id, 'out', null, $cdate, false);

            if ($out->num_rows() > 0) {
                $forms['out'] = $out->row()->employee_out_id;
            }

            $et = get_form($employee_id, 'et', null, $cdate, false);

            if ($et->num_rows() > 0) {
                $forms['et'] = $et->row()->employee_et_id;
            }

            if($this->config->item('allow_ds') == 1)
            {
                $ds = get_form($employee_id, 'ds', null, $cdate, false);

                if($ds->num_rows() > 0) {
                    $forms['ds'] = $ds->row()->employee_ds_id;
                }
            }

            if (count($forms) > 0) {
                $cell[10] = '<span class="icon-group" style="float:right"><a class="icon-button icon-16-info" rel="' . base64_encode(serialize($forms)) . '" tooltip="View Forms" href="javascript:void(0)"></a></span>';

            }

            if (strtolower($cell[1]) != 'absent' 
                && strtotime($cdate) < strtotime(date('Y-m-d'))
                && $cell[3] != 'Rest Day'
                && !$holiday
                ) {

                if(strtotime($dtr->row()->date) <= strtotime(date('Y-m-d'))){
                    if (!$w_tin):
                        $cell[1] = "Absent";
                    endif;
                    if (!$w_tout):
                        $cell[2] = "Absent";
                    endif;
                }else{
                        $cell[1] = "";
                        $cell[2] = "";
                }

                if( $cell[1] == 'Absent' && $cell[2] == 'Absent' && $this->config->item('hide_sup_absent') && $supervisor ){
                    $cell[1] = $cell[2] = "";
                }
            }

            if ($w_tin && !$w_tout){                        
                $cell[2] = "No Time Out";
            }

            if (!$w_tin && $w_tout){
                $cell[1] = "No Time In";
            }

            if ($dtr && $dtr->num_rows() > 0 && !$w_tin){
                if ($dtr->row()->awol) {
                    if ($cell[3] != 'Rest Day') {
                        if (!$supervisor){
                            if (!$w_tout){
                                if(strtotime($dtr->row()->date) <= strtotime(date('Y-m-d'))){
                                    $cell[1] = 'Absent';
                                }else{
                                    $cell[1] = '';
                                }
                            }
                        }
                    }
                }
            }

            //suspended
            if ($dtr && $dtr->num_rows() > 0){
                if ($dtr->row()->suspended) {
                        $cell[1] = 'Suspended';
                }
            }

            $a_h = array();

            $holiday_exclude = $this->system->holiday_check($cdate, $employee_id, true);

            if ($holiday) {
                foreach ($holiday as $h) {
                    $a_h[] = $h['holiday'];
                }

                if ($cell[3] == 'Rest Day') {
                    $cell[3] = '<strong>HOLIDAY / REST DAY</strong>';
                    $rd = true;
                } else {
                    $cell[3] = '<strong>HOLIDAY</strong>';
                }

                $cell[3] .= '<br />' . implode(', ', $a_h);

                if(!$holiday_exclude && !$rd){

                    if( $this->hdicore->is_flexi($employee_id) && $this->config->item('with_flexi') ) {
                        $cell[3] .= '<br /><i>Flexible</i>';
                    }
                    
                    $cell[3] .= '<br /><small><i>'.$shift.'</i></small>';
                }
            }

            if (strtotime($employed_date) > strtotime($cdate)) {
                $cell[1] = $cell[2] = '';
                if ($this->config->item('client_no') == 2){
                    $cell[4] = $cell[6] = $cell[8] = '0.00';
                }
            }

            if( $supervisor ){
                $cell[6] = '-'; // lates
                $cell[8] = '-'; // undertime
                // $cell[8] = '-'; // overtime 
            }

            //resigned
            if ($dtr && $dtr->num_rows() > 0){
                if ($dtr->row()->resigned) {
                    $cell[1] = 'Resigned';
                    $cell[3] = '-';
                    $cell[4] = '-'; 
                    $cell[5] = '-';
                    $cell[6] = '-'; 
                    $cell[7] = '-'; 
                    $cell[8] = '-'; 
                    $cell[9] = '-';
                }
            }

            // check if floating
            if($this->config->item('with_floating') == 1)
            {
                if($this->hdicore->check_if_floating_period($employee_id, $cdate))
                {
                    $cell[1] = 'Floating';
                    $cell[3] = '-';
                    $cell[4] = '-'; 
                    $cell[5] = '-'; 
                    $cell[6] = '-'; 
                    $cell[7] = '-'; 
                    $cell[8] = '-'; 
                    $cell[9] = '-';
                }
            }

            if($this->config->item('allow_ds') == 1)
            {
                if($this->hdicore->use_double_shift($employee_id, $cdate))
                {
                    $cell[1] = 'Double';
                    $cell[2] = 'Shift';
                }
            }

            if ((($dtr_in != "" && $dtr_in != '0000-00-00 00:00:00') && ($dtr_out != "" && $dtr_out != '0000-00-00 00:00:00')) && $cdate <> date ('Y-m-d',strtotime($dtr_out))){
                $cell[1] = date('M j Y h:i:s a', strtotime($dtr_in));
                $cell[2] = date('M j Y h:i:s a', strtotime($dtr_out));
            }

            if ($schedule->considered_halfday && (($dtr_in == "" || $dtr_in == '0000-00-00 00:00:00') && ($dtr_out == "" || $dtr_out == '0000-00-00 00:00:00'))){
                $cell[1] = ''; // IN
                $cell[2] = ''; // OUT  
            }

            $cells[$cell_ctr++]['cell'] = $cell;

            $cdate = date('Y-m-d', strtotime('+1 day', strtotime($cdate)));
        }

        /*}*/

        $response->records = count($cells);
        $response->rows = $cells;

        // Get records

        if ($this->config->item('client_no') == 2){
            if (strtotime($employed_date) > strtotime($date_from)){
                $response->msg_type = 'attention';
                $response->msg = 'Employed date is advance with date from. IN and OUT with affected dates will display as empty<br /> from ' . $date_from .' to '. date('Y-m-d',strtotime('-1 day', strtotime($employed_date))) . ' .';
            }
        }

        $this->load->view('template/ajax', array('json' => $response));
    }
} 