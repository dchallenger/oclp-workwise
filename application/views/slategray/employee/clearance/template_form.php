<form name="print-coe">
  <input name="coe_clearance_id" type="hidden" value="<?php echo $coe->employee_clearance_id?>">
  <h3 class="form-head">Print Certificate of Employment</h3>
  <div class="align-left" style="width:85%; min-width:400px;">
    <div class="select-input-wrap">
      <select name="coe_template_id">
        <option value="">Select...</option>
        <?php
          $template = array(
           'coe'
          );
          $this->db->where_in('code', $template);
          $this->db->where('deleted', 0);
          $templates = $this->db->get('template');
          foreach($templates->result() as $template){
            echo '<option value="'.$template->template_id.'">'.$template->templatename.'</option>';
          }
        ?>
      </select>
    </div>
  </div>
  <div class="form-submit-btn align-right nopadding" style="width:15%">
    <div class="icon-label-group">
      <div class="icon-label">
        <a onclick="print_selected_coe()" class="icon-16-print" href="javascript:void(0)">                        
          <span>Print</span>
        </a>            
      </div>
    </div>
  </div>
  <div class="clear"></div>
</form>