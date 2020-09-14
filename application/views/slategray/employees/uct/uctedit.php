<input type="hidden" class="input-text" name="department_get" value="<?= $this->input->post('record_id') ?>">
<?php
   $primary_change=false;
   $this->db->where('department_id', $this->input->post('record_id'));
   $query=$this->db->get('employee_unit_tree');


   if($query->num_rows()===0){
      //$this->db->select('user_position.position_level_id, user_position.position, user_company_department.department_id, user.firstname, user.middlename, user.lastname');
      $this->db->join('user_position','user.position_id = user_position.position_id');
      $this->db->join('employee','employee.employee_id = user.employee_id');
      $this->db->join('employee_alternate_contact','employee_alternate_contact.employee_id = user.employee_id', 'left');
      $this->db->join('cities','employee.pres_city = cities.city_id','left');
      $this->db->where('user_position.position_level_id',2);
      $this->db->where('user.department_id',$this->input->post('record_id'));
      $primary=$this->db->get('user')->result_array();
   }
   else
   {
      $should_be_primary=$query->row_array();
      $this->db->select('cities.*, user.*, user_position.*, employee.*, employee_alternate_contact.alternate_contact');
      $this->db->join('user_position','user.position_id = user_position.position_id');
      $this->db->join('employee','employee.employee_id = user.employee_id');
      $this->db->join('employee_alternate_contact','employee_alternate_contact.employee_id = user.employee_id', 'left');
      $this->db->join('cities','employee.pres_city = cities.city_id','left');
      //$this->db->where('user_position.position_level_id',2);
      $this->db->where('employee.employee_id',$should_be_primary['primary_contact']);
      //$this->db->where('user.department_id',$this->input->post('record_id'));
      $primary=$this->db->get('user')->result_array();
      $primary_change=true;
   }
   foreach($primary as $primary_field => $primary_val){
?>
<h3 class="form-head">Primary Contact</h3><br />
<div class="col-1-form view">
<div class="form-item view odd ">
   <label for="pres_address1" class="label-desc view gray"> Employee Name </label>
   <div class="text-input-wrap">
      <?php 
           if($primary_val['firstname']!=="" || $primary_val['middlename']!=="" || $primary_val['middlename']!=="") 
            echo $primary_val['firstname']." ".$primary_val['middlename']." ".$primary_val['lastname'];
           else 
            echo "&nbsp;"; 
      ?>
   </div>        
</div>
<div class="form-item view even ">
   <label for="pres_address1" class="label-desc view gray"> Primary Contact Number </label>
   <div class="text-input-wrap">
      <?php if($primary_val['mobile']!=="") echo $primary_val['mobile']; 
      else echo "&nbsp;";?>
   </div>        
</div>
<div class="form-item view odd ">
   <label for="pres_address1" class="label-desc view gray"> Alternate Contact Number </label>
   <div class="text-input-wrap">
      <?php 
         if($primary_val['home_phone']!==""){
            $alternate = $primary_val['home_phone'];
            if ($primary_val['alternate_mobile'] !==""){
               $alternate .= " / " . $primary_val['alternate_mobile'];
            }
            echo $alternate;
         }
         else {
            echo "&nbsp;";
         }
      ?>
   </div>        
</div>
<div class="form-item view even ">
   <label for="pres_address1" class="label-desc view gray"> Address 1 </label>
   <div class="text-input-wrap">
      <?php if($primary_val['pres_address1']!=="") echo $primary_val['pres_address1']; 
      else echo "&nbsp;";?>
   </div>        
</div>
<div class="form-item view odd ">
   <label for="pres_address1" class="label-desc view gray"> Address 2 </label>
   <div class="text-input-wrap">
      <?php if($primary_val['pres_address2']!=="") echo $primary_val['pres_address2']."&nbsp;"; 
      else echo "&nbsp;";?>
   </div> 
 </div>
 <div class="form-item view even ">
   <label for="pres_address1" class="label-desc view gray"> City </label>
   <div class="text-input-wrap">
      <?php if($primary_val['city']!=="")  echo $primary_val['city']; 
      else echo "&nbsp;";?>
   </div>        
</div>
<div class="form-item view odd ">
   <label for="pres_address1" class="label-desc view gray"> Province </label>
   <div class="text-input-wrap">
      <?php if($primary_val['pres_province']!=="") echo $primary_val['pres_province']."&nbsp;"; 
      else echo "&nbsp;";?>
   </div>        
</div>
<div class="form-item view odd " style="display:none">
   <label for="pres_address1" class="label-desc view gray"> Alternate Contact Number </label>
   <div class="text-input-wrap">
      <input type="text" class="input-text" name="alternate[]" value="<?=$primary_val['alternate_contact'];?>">
   <input style="display:none" type="text" class="input-text" name="em_id[]" value="<?= $primary_val['employee_id']; ?>">
   </div>        
</div>
</div>
<?php
   }//end foreach
     $this->db->where('department_id', $this->input->post('record_id'));
   $query=$this->db->get('employee_unit_tree');

   if($query->num_rows()!==0){
      $should_be_alternate=$query->row_array();
      //echo $should_be_alternate['alternate_contact'];
      $pieces=explode(", ", $should_be_alternate['alternate_contact']);
      echo "<h3 class='form-head'>Alternate Contact</h3><br />";
      foreach($pieces as $to_be_shown)
      {
         //echo $to_be_shown;
            //$this->db->select('user_position.position_level_id, user_position.position, user_company_department.department_id, user.firstname, user.middlename, user.lastname');
            $this->db->join('user_position','user.position_id = user_position.position_id');
            $this->db->join('employee','employee.employee_id = user.employee_id');
            $this->db->join('cities','employee.pres_city = cities.city_id','left');
            //$this->db->join('employee_unit_tree','employee_unit_tree.employee_id = user.employee_id', 'left');
            //$this->db->where('user_position.position_level_id',2);
            $this->db->where('user.employee_id',$to_be_shown);
            $alternate=$this->db->get('user')->result_array();


   // $this->db->select('user.*, user_position.*, employee.*, employee_alternate_contact.alternate_contact');
   // $this->db->join('user_position','user.position_id = user_position.position_id');
   // $this->db->join('employee','employee.employee_id = user.employee_id');
   // $this->db->join('employee_alternate_contact','employee_alternate_contact.employee_id = user.employee_id', 'left');

   // if($primary_change)
   //    $this->db->where('employee.employee_id <>',$should_be_primary['primary_contact']);
   // else
   //    $this->db->where('user_position.position_level_id <>',2);

   // $this->db->where('user.department_id',$this->input->post('record_id'));
   // $alternate=$this->db->get('user')->result_array();
   // echo '<h3 class="form-head">Alternate Contact</h3><br />';

      foreach($alternate as $alternate_field => $alternate_val){
?>
<div class="col-1-form view">
<div class="form-item view odd ">
   <label for="pres_address1" class="label-desc view gray"> Employee Name </label>
   <div class="text-input-wrap">
      <?php 
           if($alternate_val['firstname']!=="" || $alternate_val['middlename']!=="" || $alternate_val['middlename']!=="") 
            echo $alternate_val['firstname']." ".$alternate_val['middlename']." ".$alternate_val['lastname'];
           else 
            echo "&nbsp;"; 
      ?>
   </div>        
</div>
<div class="form-item view odd ">
   <label for="pres_address1" class="label-desc view gray"> Primary Contact Number </label>
   <div class="text-input-wrap">
      <?php if($alternate_val['mobile']!=="")  echo $alternate_val['mobile']; 
      else echo "&nbsp;";?>
   </div>        
</div>
<div class="form-item view odd ">
   <label for="pres_address1" class="label-desc view gray"> Alternate Contact Number </label>
   <div class="text-input-wrap">
      <?php 
         if($alternate_val['home_phone']!==""){
            $alternate = $alternate_val['home_phone'];
            if ($alternate_val['alternate_mobile'] !==""){
               $alternate .= " / " . $alternate_val['alternate_mobile'];
            }
            echo $alternate;
         }
         else {
            echo "&nbsp;";
         }
      ?>      
   </div>        
</div>
<div class="form-item view odd ">
   <label for="pres_address1" class="label-desc view gray"> Address 1 </label>
   <div class="text-input-wrap">
      <?php if($alternate_val['pres_address1']!=="")  echo $alternate_val['pres_address1']; 
      else echo "&nbsp;";?>
   </div>        
</div>
<div class="form-item view odd ">
   <label for="pres_address1" class="label-desc view gray"> Address 2 </label>
   <div class="text-input-wrap">
      <?php if($alternate_val['pres_address2']!=="")  echo $alternate_val['pres_address2']; 
      else echo "&nbsp;";?>
   </div> 
 </div>
 <div class="form-item view odd ">
   <label for="pres_address1" class="label-desc view gray"> City </label>
   <div class="text-input-wrap">
      <?php if($alternate_val['city']!=="")   echo $alternate_val['city']; 
      else echo "&nbsp;";?>
   </div>        
</div>
<div class="form-item view odd ">
   <label for="pres_address1" class="label-desc view gray"> Province </label>
   <div class="text-input-wrap">
      <?php if($alternate_val['pres_province']!=="")  echo $alternate_val['pres_province']; 
      else echo "&nbsp;";?>
   </div>        
</div>
<div class="form-item view odd " style="display:none">
   <label for="pres_address1" class="label-desc view gray"> Alternate Contact Number </label>
   <div class="text-input-wrap">
      <input type="text" class="input-text" name="alternate[]" value="<?=$alternate_val['alternate_contact'];?>">
   <input style="display:none" type="text" class="input-text" name="em_id[]" value="<?= $alternate_val['employee_id'] ?>">
   </div>        
</div>
</div>
<?php
         }
      }
   }
?>