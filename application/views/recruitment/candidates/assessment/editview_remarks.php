<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

    $date_start = ($appraisal->date_from && $appraisal->date_from != "0000-00-00") ? date('m/d/Y', strtotime($appraisal->date_from)) : "" ;
    $date_send = ($appraisal->date_to && $appraisal->date_to != "0000-00-00") ? date('m/d/Y', strtotime($appraisal->date_to)) : "" ;
    $app_benefits = json_decode($appraisal->benefits, true);
    $app_others = json_decode($appraisal->other_benefits, true);
    
?>
    <br>
    <div class="form-item odd ">
        <label class="label-desc gray" for="company">
            Company:<span class="red font-large">*</span>
        </label>
        <div class="text-input-wrap">   
           <input type="text" name="company" value="<?=$appraisal->company?>" class="input-text company">
        </div>
    </div>
    <div class="form-item even ">
        <label class="label-desc gray" for="salary">
            Basic Salary:
        </label>
        <div class="text-input-wrap">   
           <input type="text" name="salary" value="<?=number_format($appraisal->salary, '2', '.', ',')?>" class="input-text salary text-right">
        </div>
    </div>
    <div class="form-item odd ">
        <label class="label-desc gray" for="industry">
            Industry:
        </label>
        <div class="text-input-wrap">   
           <input type="text" name="industry" value="<?=$appraisal->industry?>" class="input-text industry">
        </div>
    </div>
    <div class="form-item even ">
    <label class="label-desc gray" for="forms_period">Employment Period:</label>
      <div class="text-input-wrap">
        <input type="text" name="employment_period_start" id="date_start" value="<?=$date_start?>" style="width:30%;" class="input-text date"/>
        &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;
        <input type="text" name="employment_period_end" id="date_end" style="width:30%;" value="<?= $date_send?>" class="input-text date"/>
      </div>
  </div>
    <div class="form-item odd ">
        <label class="label-desc gray" for="position">
            Position:
        </label>
        <div class="text-input-wrap">   
           <input type="text" name="position" value="<?=$appraisal->position?>" class="input-text position">
        </div>
    </div>
    <div class="form-item even ">
        <label class="label-desc gray" for="prev_status">
            Employment Status:
        </label>
        <div class="text-input-wrap">   
           <input type="text" name="prev_status" value="<?=$appraisal->previous_emp_status?>" class="input-text prev_status">
        </div><br><br>
    </div>
    <div class="form-item odd ">
        <label class="label-desc gray" for="level">
            Level:
        </label>
        <div class="text-input-wrap">   
           <input type="text" name="level" value="<?=$appraisal->level?>" class="input-text level">
        </div>
    </div>


<div class="form-submit-btn align-right nopadding">
    <div class="icon-label-group">
    <div class="icon-label">
      <a onclick="add_benefit()" class="icon-16-add" href="javascript:void(0)">                        
        <span>Add Benefit</span>
      </a>            
    </div>
  </div>
</div>
<div class="select-input-wrap align-right" style="width: auto;">
    <input type="hidden" value="" name="selected-benefits">

  <select name="benefitddlb" id="benefitddlb"> 
        <option value="">Select...</option>
    <?php
    if ($app_benefits && count($app_benefits['benefit_id']) > 0)
    {
        $where = 'recruitment_other_benefits_id NOT IN (' .implode(',', $app_benefits['benefit_id']) . ')';
        $this->db->where($where);
    }
    $benefits = $this->db->get_where('recruitment_other_benefits', array('deleted' => 0));
    foreach($benefits->result() as $benefit): ?>
      <option value="<?php echo $benefit->recruitment_other_benefits_id?>"><?php echo $benefit->benefits_from?></option><?php
    endforeach; ?>
    <option value="0">Others</option>
  </select>

</div>


<div class="spacer"></div>

<div class="clear"></div>
<div class="benefits-div"></div>
<h3 class="form-head"></h3>
<?php 
if ($app_benefits && count($app_benefits['benefit']) > 0):
            foreach ($app_benefits['benefit'] as $key => $value):
?>

 <div class="form-item odd ">
      <label class="label-desc gray" for="basic"><?=$value?>:</label>
      <div class="text-input-wrap">
        <input type="hidden" name="benefit[benefit_id][]" value="<?=$app_benefits['benefit_id'][$key]?>">
        <input type="hidden" name="benefit[benefit][]" value="<?=$value?>">
        <input type="text" class="benefit-field input-text input-medium text-right" value="<?=$app_benefits['benefit_amount'][$key]?>" id="" name="benefit[benefit_amount][]" />
        <span class="icon-group">   
          <a onclick="delete_benefit( $(this),  <?php echo $benefit_id?>)" href="javascript:void(0);" class="icon-button icon-16-minus"></a>
        </span>
      </div>
    </div>
  <div class="clear"></div>
<?php 
        endforeach;
    endif;

?>

<?php 
if ($app_others && count($app_others['benefit']) > 0):
            foreach ($app_others['benefit'] as $other => $others):
?>

  <div class="form-item odd ">
    <label class="label-desc gray" for="basic">Others:</label>
    <div class="text-input-wrap">
      <input type="text" class="input-text input-medium" value="<?=$others?>" id="" name="others[benefit][]" /></div>

      <label class="label-desc gray" for="basic">Amount:</label>
      <div class="text-input-wrap">
      <input type="text" class="benefit-field input-text input-medium text-right" value="<?=$app_others['amount'][$other]?>" id="" name="others[amount][]" />
      <span class="icon-group">   
        <a onclick="delete_benefit( $(this),  <?=$other?>)" href="javascript:void(0);" class="icon-button icon-16-minus"></a>
      </span>
    </div>
  </div>
  <div class="clear"></div>


<?php 
        endforeach;
    endif;

?>

