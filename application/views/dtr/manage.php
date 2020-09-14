<form action="" method="post" id="export-form">
  <div id="form-div">
    <div class="col-2-form">
      <div class="form-item odd ">
        <label for="department" class="label-desc gray">Year:</label>
        <div class="select-input-wrap"><?php
          $this->db->select('period_year');
          $this->db->group_by('period_year');
          $period_year = $this->db->get('timekeeping_period'); 
          if ($period_year->num_rows() > 0): ?>
            <select name="period_year" style="width:400px;" id="period_year"><?php
              foreach ($period_year->result() as $row){
                print '<option value="'.$row->period_year.'" '.(date('Y') == $row->period_year ? 'selected="selected"' : '').'>'.$row->period_year.'</option>';
              } ?>
            </select><?php
          endif; ?>
        </div>
      </div>
      <div class="form-item odd ">
        <label for="department" class="label-desc gray">Period:</label>
        <div class="select-input-wrap">
          <select name="period_id" style="width:400px;" id="period_id"></select>
        </div>
      </div>

      <div class="form-item even ">
        <label for="department" class="label-desc gray">Employee:</label>
        <div class="select-input-wrap">
          <?php
              $options = array('' => array( '' => 'Select...'));

              if(!$is_project_hr){
                $qry = "SELECT a.*, b.department
                FROM {$this->db->dbprefix}user a
                LEFT JOIN {$this->db->dbprefix}user_company_department b on b.department_id = a.department_id
                LEFT JOIN {$this->db->dbprefix}employee c on c.employee_id = a.user_id
                where a.deleted = 0 AND a.inactive = 0 AND c.resigned = 0";

                if(CLIENT_DIR == 'asianshipping'){
                  // only for asian shipping
                   $qry .= ' AND a.company_id = 1';
                }

                $emps = $this->db->query($qry);

                foreach($emps->result() as $employee){
                  $options[$employee->department][$employee->user_id] = $employee->firstname . ' ' .$employee->lastname;
                }
              }else{
                $emps = $project_hr;
                foreach($emps as $employee){
                  $options[$employee['department']][$employee['user_id']] = $employee['firstname'] . ' ' .$employee['lastname'];
                }              
              }

              echo form_dropdown('dtr-employee_id', $options);
            ?>
        </div>
      </div>

      <div class="spacer"></div>
    </div>
    <div class="clear"></div>
    <div class="spacer"></div>
  </div>
</form>

<div id="dtr-container"></div>