<?php 
include (APPPATH . 'controllers/forms/obt.php');

class Firstbalfour_obt extends Obt {

    function get_sub_by_project()
    {

            $response->is_projecthr = false;

            $subordinates = $this->system->get_subordinates_by_project($this->input->post('project_hr'));

            $response->subordinates = '';
            if ($this->user_access[$this->module_id]['project_hr']) {
                if (count($subordinates)>0 && $subordinates != false) {
                	$response->subordinates .= '<option value=" "> </option>';
                    foreach ($subordinates as $sub) {
                        $response->subordinates .= '<option value="'.$sub['employee_id'].'">'.$sub['firstname'] .' '. $sub['lastname'].'</option>';
                    }
                    $response->sub_count = count($subordinates);
                   	$response->is_projecthr = true;
                }
                //commented to view all employees if user has post-control and is not a project HR
                // else{
                //     $employee = $this->system->get_employee($this->input->post('project_hr'));
                //     $response->employee_id = $employee['employee_id'];
                //     $response->employee = $employee['firstname'] .' '. $employee['lastname'];
                // }
            }
        $data['json'] = $response;                      
        $this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
    }

}

/* End of file firstbalfour_obt.php */
/* Location: ./application/controllers/firstbalfour_obt.php */