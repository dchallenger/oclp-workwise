<form name="print-jocontract">
  <input name="jocontract-job_offer_id" type="hidden" value="<?php echo $jo->job_offer_id?>">
  <input name="jocontract-record_id" type="hidden" value="<?php echo $jo->candidate_id?>">
  <h3 class="form-head">Print Contract</h3>
  <div class="align-left" style="width:82%">
    <div class="select-input-wrap">
      <select name="contract_template_id">
        <option value="">Select...</option>
        <?php
          $this->db->where('module_id', $this->module_id);
          $this->db->where('code !=', 'hiring_approval');
          $this->db->where('deleted', 0);
          $templates = $this->db->get('template');
        
          foreach($templates->result() as $template){
            echo '<option value="'.$template->template_id.'">'.$template->templatename.'</option>';
          }
        ?>
      </select>
    </div>
  </div>
  <div class="form-submit-btn align-right nopadding" style="width:18%">
    <div class="icon-label-group">
      <div class="icon-label">
        <a onclick="print_contract()" class="icon-16-print" href="javascript:void(0)">                        
          <span>Print</span>
        </a>            
      </div>
    </div>
  </div>
  <div class="clear"></div>
</form>