<?php
  $is_group = $this->db->get_where('user_position', array('deleted' => 0, 'position_id' => $this->userinfo['position_id']))->row();
  $portlet_config = unserialize($is_group->portlet_config);

  $where_in = array();
if(!$this->user_access[$this->module_id]['post']) {
  $where_in = $this->system->get_employee_circle( $this->user->user_id,3);
}

$cirle = "";
if( sizeof($where_in) > 0){
  $cirle = ' AND '.$this->db->dbprefix.'user.user_id IN ('.implode(',' , $where_in).')';
}

if($portlet_config[3]['access'] == 'personal') {
  $user_id = $this->user->user_id;
  $cirle .= " AND user.user_id = '{$user_id}'";
}

$sql = '(SELECT CONCAT('.$this->db->dbprefix.'user.firstname, " ", '.$this->db->dbprefix.'user.lastname) AS employee,
          aux, 
          "OBT" AS reason, 
          '.$this->db->dbprefix.'user_company_department.department AS department
        FROM '.$this->db->dbprefix.'employee_obt 
          JOIN '.$this->db->dbprefix.'user 
            ON '.$this->db->dbprefix.'user.employee_id = '.$this->db->dbprefix.'employee_obt.employee_id
          LEFT JOIN '.$this->db->dbprefix.'user_company_department 
            ON '.$this->db->dbprefix.'user.department_id = '.$this->db->dbprefix.'user_company_department.department_id  
        WHERE form_status_id = 3 AND ("' . date('Y-m-d') . '" BETWEEN date_from AND date_to) '.$cirle.' )
        UNION 
        (SELECT CONCAT('.$this->db->dbprefix.'user.firstname, " ", '.$this->db->dbprefix.'user.lastname) AS employee,
          aux, 
          '.$this->db->dbprefix.'employee_form_type.application_form AS reason, 
          '.$this->db->dbprefix.'user_company_department.department AS department
        FROM '.$this->db->dbprefix.'employee_leaves 
          JOIN '.$this->db->dbprefix.'user 
            ON '.$this->db->dbprefix.'user.employee_id = '.$this->db->dbprefix.'employee_leaves.employee_id
          LEFT JOIN '.$this->db->dbprefix.'user_company_department 
            ON '.$this->db->dbprefix.'user.department_id = '.$this->db->dbprefix.'user_company_department.department_id  
          JOIN '.$this->db->dbprefix.'employee_form_type 
            ON '.$this->db->dbprefix.'employee_leaves.application_form_id = '.$this->db->dbprefix.'employee_form_type.application_form_id
        WHERE ( application_code NOT IN ("CLE") ) AND form_status_id = 3 AND ("' . date('Y-m-d') . '" BETWEEN date_from AND date_to) '.$cirle.')
        ORDER BY 1';  
$result = $this->db->query($sql);

?>
<table id="portlet-table" class="ooo-table" width="100%" border="0">
  <?php if ($result->num_rows() == 0):?>
  <tr><td>None as of this moment.</td></tr>  
  <?php ;else:?>
  <?php foreach ($result->result() as $employee):?>
  <tr>    
    <td width="54%"><strong><?=$employee->employee . ($employee->aux != '' ? ',' . $employee->aux : '') ?></strong><br /><small><?=$employee->department?></small></td>
    <td width="33%" align="right"><?=$employee->reason?></td>
  </tr>
<?php endforeach;?>
<?php endif;?>
</table>
<div class="spacer"></div>
<?php if ($result->num_rows() > 5):?>
<div class="icon-label-group align-right">                          
        <a href="javascript:void(0);" id="ooo-show-more"><span>Show More</span> </a>      
</div>
<?php endif;?>

<script>
  $('.ooo-table tr').not(':lt(5)').hide();
  $('#ooo-show-more').die().click(function() {
    $('.ooo-table tr:hidden:lt(5)').fadeIn('slow');
    if ($('.ooo-table :hidden').size() == 0) {
      $('#ooo-show-more').hide();
    }
  });
</script>
