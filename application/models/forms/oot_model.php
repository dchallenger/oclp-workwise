<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Oot_model extends MY_Model
{
	function __construct()
	{
		parent::__construct();
	}

    function _get_inclusive_worksched(){
        if(IS_AJAX){
            $employee_id = $this->input->post('employee_id');
            $data = $this->system->get_employee_worksched_shift($this->input->post('employee_id'), $this->input->post('date'));
            $data->date = date('Y-m-d', strtotime($this->input->post('date')));
            
            if(isset($data->semird_type_id) && $data->semird_type_id == 1) $data->shift_id = 0;

            if($data->shift_id != 0){
                $timestart = strtotime($data->shifttime_start);
                $timeend = strtotime($data->shifttime_end);
                $data->shifttime_start =  date('F j, Y', strtotime($data->date)) . ' ' . $data->shifttime_start;
                if( $timeend < $timestart ){
                    //add 1 day
                    $data->shifttime_end = date('F j, Y', strtotime($data->date.' +1 day ')) . ' ' . $data->shifttime_end;
                }
                else{
                    $data->shifttime_end =  date('F j, Y', strtotime($data->date)) . ' ' . $data->shifttime_end;
                }

                 if( floatval( $data->max_preshift_ot ) == 0 && floatval( $data->max_postshift_ot ) == 0 ){
                    $next_working_day = $this->system->get_next_working_day( $employee_id, $data->date );
                    $next_shift = $this->system->get_employee_worksched_shift($employee_id, $next_working_day);
                    $next_shifttime = $next_working_day.' '.$next_shift->shifttime_start;  

                    $ot = strtotime( $next_shifttime) - strtotime($data->shifttime_end);
                    $data->max_preshift_ot = 0; 
                    $data->max_postshift_ot = $ot / 60 / 60;
                }
            }
            else{
                $next_working_day = $this->system->get_next_working_day( $employee_id, $data->date );
                $next_shift = $this->system->get_employee_worksched_shift($employee_id, $next_working_day);
                $next_shifttime = $next_working_day.' '.$next_shift->shifttime_start;
                $data->shifttime_start =  date('F j, Y', strtotime($data->date)) . ' ' . '00:0:00';
                $data->shifttime_end =  date('F j, Y', strtotime($data->date)) . ' ' . '00:0:00';
                
                $ot = strtotime( $next_shifttime) - strtotime($data->shifttime_end);
                $data->max_preshift_ot = 0; 
                $data->max_postshift_ot = $ot / 60 / 60;
            }

            //check if holiday
            $holiday_check = $this->system->holiday_check(date('Y-m-d', strtotime($data->date)));

            if($holiday_check){
                $data->holiday = true;
            }
            else{
                $data->holiday = false;
            }

            //fix bug with decimals in subtracting time from date time
            $hr = floor($data->max_preshift_ot);
            $min = round(60*($data->max_preshift_ot-$hr));
            $data->max_preshift_ot = ($hr*60) + $min;
            $hr = floor($data->max_postshift_ot);
            $min = round(60*($data->max_postshift_ot-$hr));
            $data->max_postshift_ot = ($hr*60) + $min;
            $data->preot_start = date('F j, Y H:i:s', strtotime($data->shifttime_start. ' -'. $data->max_preshift_ot .' minutes'));
            $data->postot_end = date('F j, Y H:i:s', strtotime($data->shifttime_end. ' +'. $data->max_postshift_ot .' minutes'));
            $this->load->view('template/ajax', array('json' => $data));
        }
        else{
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);  
        }
    }
}