<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

    $date_start = ($appraisal->date_from && $appraisal->date_from != "0000-00-00") ? date('m/d/Y', strtotime($appraisal->date_from)) : "" ;
    $date_send = ($appraisal->date_to && $appraisal->date_to != "0000-00-00") ? date('m/d/Y', strtotime($appraisal->date_to)) : "" ;
    $app_benefits = json_decode($appraisal->benefits, true);
    $app_others = json_decode($appraisal->other_benefits, true);
    
?>
    <br>

    <div class="form-item odd view ">
        <label class="label-desc view gray" for="company">
            Company:
        </label>
        <div class="text-input-wrap">   <?=$appraisal->company?></div>
    </div>
    <div class="form-item even view">
        <label class="label-desc view gray" for="salary">
            Basic Salary:
        </label>
        <div class="text-input-wrap">   <?=$appraisal->salary //number_format($appraisal->salary, '2', '.', ',')?></div>
    </div>
    <div class="form-item odd view ">
        <label class="label-desc gray view" for="industry">
            Industry:
        </label>
        <div class="text-input-wrap">   <?=$appraisal->industry?></div>
    </div>
    <div class="form-item even view ">
    <label class="label-desc gray view" for="forms_period">Employment Period:</label>
      <div class="text-input-wrap"><?=$date_start?>&nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;<?= $date_send?>
      </div>
  </div>
    <div class="form-item odd view ">
        <label class="label-desc gray view" for="position">
            Position:
        </label>
        <div class="text-input-wrap">   <?=$appraisal->position?></div>
    </div>
    <div class="form-item odd view">
        <label class="label-desc view gray" for="level">
            Level:
        </label>
        <div class="text-input-wrap">   <?=$appraisal->level?></div>
    </div>


<div class="clear"></div>
<div class="benefits-div"></div>
<h3 class="form-head"></h3>
<?php 
if ($app_benefits && count($app_benefits['benefit']) > 0):
            foreach ($app_benefits['benefit'] as $key => $value):
?>

 <div class="form-item odd ">
      <label class="label-desc view gray" for="basic"><?=$value?>:</label>
      <div class="text-input-wrap">
        <?=$app_benefits['benefit_amount'][$key]?>
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

  <div class="form-item  view odd ">
    <label class="label-desc view gray" for="basic">Others:</label>
    <div class="text-input-wrap"><?=$others?></div>
    </div>
<div class="form-item  view odd ">
    <label class="label-desc view gray" for="basic">Amount:</label>
    <div class="text-input-wrap">  <?=$app_others['amount'][$other]?></div>
  </div>
  <div class="clear"></div>


<?php 
        endforeach;
    endif;

?>