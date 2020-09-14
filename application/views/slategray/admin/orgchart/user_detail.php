<?php
	$avatar = (!empty($userdata->photo) && file_exists($userdata->photo) ) ? $userdata->photo : $this->userinfo['theme'].'/images/no-photo.jpg';
?>
<table cellpadding="10" cellspacing="10" border="0" width="500px">
	<tr>
  	<td style="padding:10px;" align="center"><img src="<?php echo base_url().$avatar ?>"></td>
    <td style="vertical-align:top; padding: 10px">
    	<h3><?php echo $userdata->firstname.' '.$userdata->lastname?></h3>
      <p><?php echo $userdata->position?></p>
      <p>&nbsp;</p>
      <table cellpadding="10" cellspacing="10" border="0" width="100%"> 
      	<colgroup>
        	<col width="30%" />
          <col width="5%" />
          <col width="65%" />
        </colgroup>
      	<tr>
        	<td>Department</td>
          <td>:</td>
          <td><?php echo $userdata->department?></td>
        </tr>
        <tr>
        	<td>Email</td>
          <td>:</td>
          <td><?php echo $userdata->email?></td>
        </tr>
        <tr>
        	<td>Nickname</td>
          <td>:</td>
          <td><?php echo $userdata->nickname?></td>
        </tr>
        <tr>
        	<td>Telephone No</td>
          <td>:</td>
          <td><?php echo $userdata->telephone?></td>
        </tr>
        <tr>
        	<td>Birthday</td>
          <td>:</td>
          <td><?php echo date( $this->config->item( 'display_date_format' ), strtotime( $userdata->birth_date ) )?></td>
        </tr>
        <tr>
        	<td>Date hired</td>
          <td>:</td>
          <td>
						<?php 
						$employee = $this->db->get_where('employee', array('user_id' => $this->input->post('user_id')));
						if($employee->num_rows() > 0 ){
							$employee = $employee->row();
							if( $employee->employed_date != "0000-00-00" || $employee->employed_date != '' ) echo date( $this->config->item( 'display_date_format' ), strtotime( $employee->employed_date ) ) ;
						}
						?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>