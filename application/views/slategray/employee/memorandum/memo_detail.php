<?php
	$posted_by = $this->db->get_where('user', array('user_id' => $memo->created_by))->row();
	
	switch( $memo->memo_type_id ){
		case 1;
			$heading = $memo->memo_title;
			break;
		case 2;
			$this->db->select('user.*, user_company.*', $memo->employee_id);
			$this->db->where('user_id', $memo->employee_id);
			$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
			$employee = $this->db->get('user')->row();
			$heading = "New Employee Announcement: ".$employee->company;
			break;
		case 3;
			$this->db->select('user.*, user_company.*', $memo->employee_id);
			$this->db->where('user_id', $memo->employee_id);
			$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
			$employee = $this->db->get('user')->row();
			$heading = "Employee Resignation: ".$employee->company;
			break;		
	}
?>
<h2><?php echo $heading?></h2>
<span>Posted on: <?php echo date('F d, Y', strtotime($memo->publish_from))?></span> <span>By: <?php echo $posted_by->firstname.' '.$posted_by->lastname?></span>
<hr/>
<div>
	<?php echo $memo->memo_body?>
</div>