<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Obt_model extends MY_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function _validation_check(){
		$err = false;
        $msg = ""; 
        $employee_id = $this->input->post("employee_id");
        $date_from = date('Y-m-d',strtotime($this->input->post('date_from')));
        $date_to = date('Y-m-d',strtotime($this->input->post('date_to')));            
        $time_start_hh_mm =  date('H:i:s',strtotime($this->input->post('time_start')));       
        $time_end_hh_mm =  date('H:i:s',strtotime($this->input->post('time_end')));

        if (date('Y-m-d') > date('Y-m-d',strtotime($date_from))){
            if ($this->system->check_in_cutoff($date_from) == 1){
                $err = true;
                $msg = "Next payroll cutoff not yet created in processing, please contact admin.";                
            }
            elseif ($this->system->check_in_cutoff($date_from) == 2){
                $err = true;
                $msg = "Your OBT application is no longer within the allowable time.";                
            }
            else{         
                $numrows = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_obt WHERE employee_id = ".$employee_id."
                                             AND  deleted = 0 AND form_status_id=3
                                             AND (((date_from <= '".$date_from."' AND date_to >= '".$date_from."')
                                             OR (date_from <= '".$date_to."' AND date_to >= '".$date_to."'))
                                             AND ((time_start <= '".$time_start_hh_mm."' AND time_end >= '".$time_start_hh_mm."') 
                                             OR (time_start <= '".$time_end_hh_mm."' AND time_end >= '".$time_end_hh_mm."'))) ")->num_rows();
                if($numrows > 0){
                    $err = true;
                    $msg = "Official business application has already been filed.";
                }
                else{
                    $err = false;
                    $msg = "";
                }               
            }
        }
        else{            
            $numrows = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_obt WHERE employee_id = ".$employee_id."
                                         AND  deleted = 0 AND form_status_id=3
                                         AND (((date_from <= '".$date_from."' AND date_to >= '".$date_from."')
                                         OR (date_from <= '".$date_to."' AND date_to >= '".$date_to."'))
                                         AND ((time_start <= '".$time_start_hh_mm."' AND time_end >= '".$time_start_hh_mm."') 
                                         OR (time_start <= '".$time_end_hh_mm."' AND time_end >= '".$time_end_hh_mm."'))) ")->num_rows();
            if($numrows > 0){
                $err = true;
                $msg = "Official business application has already been filed.";
            }
            else{
                $err = false;
                $msg = "";
            }         
        }

        $this->load->view('template/ajax', 
            array('json' => 
                array('err' => $err, 'msg_type' => $msg)
            )
        );
	}
}