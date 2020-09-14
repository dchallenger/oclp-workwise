<?php

include (APPPATH . 'controllers/recruitment/manpower_loading_schedule.php');

class Firstbalfour_manpower_loading_schedule extends Manpower_loading_schedule
{
	public function __construct() {
		parent::__construct();
	}	

	function get_position_per_project(){

		$record_id = $this->input->post('record_id');
		$project_name_id = $this->input->post('project_name_id');


		$html = '<table class="default-table boxtype" style="width:100%" id="module-access">
           			<colgroup width="15%"></colgroup>
		            <thead>
		                <tr class="">
		                    <th style="text-align:left;" colspan="15">&nbsp;</th>
		                </tr>
		                <tr class="">
		                    <th style="vertical-align:middle">Category</th><th class="action-name font-smaller even"><div>Remarks (Head Count)</div></th><th class="action-name font-smaller even"><div>Jan</div></th><th class="action-name font-smaller odd"><div>Feb</div></th><th class="action-name font-smaller even"><div>Mar</div></th><th class="action-name font-smaller odd"><div>Apr</div></th><th class="action-name font-smaller even"><div>May</div></th><th class="action-name font-smaller odd"><div>Jun</div></th><th class="action-name font-smaller even"><div>Jul</div></th><th class="action-name font-smaller odd"><div>Aug</div></th><th class="action-name font-smaller even"><div>Sep</div></th><th class="action-name font-smaller odd"><div>Oct</div></th><th class="action-name font-smaller even"><div>Nov</div></th><th class="action-name font-smaller odd"><div>Dec</div></th></tr>
		            </thead>';
		            
		if( $record_id == '-1' ){

			$this->db->select('user.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name',false);
	    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id', 'left');
	    	$this->db->join('user_position','user.position_id = user_position.position_id', 'left');
	    	$this->db->join('employee','employee.user_id = user.user_id');
	        $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id');
	        // $this->db->join('employee_work_assignment','employee_work_assignment.employee_id = employee.employee_id', 'left');
	    	$this->db->where('user.position_id !=', 0);
	    	$this->db->where('user.project_name_id', $project_name_id);
	    	$this->db->order_by('user_rank.rank_index','DESC');
	    	$this->db->group_by('user.position_id');

	    	$result = $this->db->get('user');
	    	$position_hierarchy = $result->result_array();

			if( count($position_hierarchy) > 0 ){
				foreach( $position_hierarchy as $position_hierarchy_record ){
                    $row_info = array();
                    if ($position_hierarchy && $result->num_rows() > 0){
                        $row_info = $result->row_array();
                    }
					$html .= '
				
					       	 <tr>
                                    <td>'.$position_hierarchy_record['position'].'<input type="hidden" value="'.$position_hierarchy_record['position_id'].'" name="position_id[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['remarks'].'" name="remarks[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['jan'].'" name="jan[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['feb'].'" name="feb[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['mar'].'" name="mar[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['apr'].'" name="apr[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['may'].'" name="may[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['jun'].'" name="jun[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['jul'].'" name="jul[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['aug'].'" name="aug[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['sep'].'" name="sep[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['oct'].'" name="oct[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['nov'].'" name="nov[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['dec'].'" name="dec[]" /></td>
                                </tr>';
				        
				}
			}
			else{

				$html .= '
							<tr><td style="text-align:center; font-weight:bold;" colspan="17">No existing Position available</td></tr>';

			}

	
    	}

    	else{

	    	$record_id = $this->input->post('record_id');
			$project_name_id = $this->input->post('project_name_id');

			$this->db->join('user_position','user_position.position_id = manpower_loading_schedule_details.position_id', 'left');
			$this->db->where('manpower_loading_schedule_id', $record_id);
    		$details = $this->db->get('manpower_loading_schedule_details')->result_array();

    			foreach( $details as $row_info ){
					$html .= '
				
					       	 <tr>
                                    <td>'.$row_info['position'].'<input type="hidden" value="'.$row_info['position_id'].'" name="position_id[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['remarks'].'" name="remarks[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['jan'].'" name="jan[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['feb'].'" name="feb[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['mar'].'" name="mar[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['apr'].'" name="apr[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['may'].'" name="may[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['jun'].'" name="jun[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['jul'].'" name="jul[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['aug'].'" name="aug[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['sep'].'" name="sep[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['oct'].'" name="oct[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['nov'].'" name="nov[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['dec'].'" name="dec[]" /></td>
                                </tr>';
				        
				}

    	}

    	$html .= '</table>';
    	$data['html'] = $html;

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

	}
	function get_positions()
	{
		$record_id = $this->input->post('record_id');
		$project_name_id = $this->input->post('project_name_id');

		if($record_id == -1){
			$this->db->select('user.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name',false);
	    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id', 'left');
	    	$this->db->join('user_position','user.position_id = user_position.position_id', 'left');
	    	$this->db->join('employee','employee.user_id = user.user_id');
	        $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id');
	        // $this->db->join('employee_work_assignment','employee_work_assignment.employee_id = employee.employee_id', 'left');
	       	$this->db->where('user.position_id !=', 0);
	    	$this->db->where('user.project_name_id', $project_name_id);
	    	$this->db->order_by('user_rank.rank_index','DESC');
	    	$this->db->group_by('user.position_id');

	    	$existing_positions = $this->db->get('user')->result_array();
	    }else{

	    	$this->db->join('user_position','user_position.position_id = manpower_loading_schedule_details.position_id', 'left');
			$this->db->where('manpower_loading_schedule_id', $record_id);
    		$existing_positions = $this->db->get('manpower_loading_schedule_details')->result_array();
	    }

			$positions_array = array();
			foreach ($existing_positions as $key) {
				$positions_array[] = $key['position_id'];	
			}

			if (count($positions_array) > 0) {
				$this->db->where_not_in('position_id', $positions_array);
			}
			$this->db->where('deleted', 0);
			$positions = $this->db->get('user_position')->result();	
			$options = '';
			foreach ($positions as $position) {
				$options .= '<option value="' . $position->position_id . '">' . $position->position . '</option>';
			}
			
			$this->load->view('template/ajax', array('html' => $options));
	}

	function get_form_position(){
		$row_info = array();

		$html = '<table class="default-table boxtype" style="width:100%" >
		            <colgroup width="15%"></colgroup>';

		$html .= '<tr>
                        <td>'.$this->input->post('position').'<input type="hidden" value="'.$this->input->post('position_id').'" name="position_id[]" /></td>
                        <td><input type="text" style="width:65px" value="'.$row_info['remarks'].'" name="remarks[]" /></td>
                        <td><input type="text" style="width:100%" value="'.$row_info['jan'].'" name="jan[]" /></td>
                        <td><input type="text" style="width:100%" value="'.$row_info['feb'].'" name="feb[]" /></td>
                        <td><input type="text" style="width:100%" value="'.$row_info['mar'].'" name="mar[]" /></td>
                        <td><input type="text" style="width:100%" value="'.$row_info['apr'].'" name="apr[]" /></td>
                        <td><input type="text" style="width:100%" value="'.$row_info['may'].'" name="may[]" /></td>
                        <td><input type="text" style="width:100%" value="'.$row_info['jun'].'" name="jun[]" /></td>
                        <td><input type="text" style="width:100%" value="'.$row_info['jul'].'" name="jul[]" /></td>
                        <td><input type="text" style="width:100%" value="'.$row_info['aug'].'" name="aug[]" /></td>
                        <td><input type="text" style="width:100%" value="'.$row_info['sep'].'" name="sep[]" /></td>
                        <td><input type="text" style="width:100%" value="'.$row_info['oct'].'" name="oct[]" /></td>
                        <td><input type="text" style="width:100%" value="'.$row_info['nov'].'" name="nov[]" /></td>
                        <td><input type="text" style="width:100%" value="'.$row_info['dec'].'" name="dec[]" /></td>
                    </tr>';		
		$html .= '</table>';

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);				
	}


	function get_division_head(){

			$this->db->join('user_company_division','project_name.division_id = user_company_division.division_id');
			$this->db->where('user_company_division.deleted',0);
			$this->db->where('project_name.project_name_id', $this->input->post('project_name_id'));
			$result = $this->db->get('project_name')->row();
		
			$this->db->select('user_id, CONCAT(firstname," ",lastname) as head', false);
			$this->db->where('user_id',$result->division_manager_id);
			$div_head = $this->db->get('user')->row();

			$html = '<option value="'.$div_head->user_id.'" selected="selected">'.$div_head->head.'</option>';

		$data['html'] = $html;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	} 
// end
}