<style type="text/css">
table textarea, table input{
    font-size: 12px !important;
}
ul.rating_scale {
 list-style: none;
 margin-left: 0;
 padding-left: 1.2em;
 text-indent: -2.1em;
 }

.textarea{
    width: 250px; 
    height: 60px;
    vertical-align:middle;
}

table.valign tr td {
vertical-align:top;
}
table.valign-bottom tr td {
vertical-align:bottom;
}
table.valign form {
display:inline;
}
DIV.scrollingdatagrid {
    overflow-x:auto;
    overflow-y:auto;
    position:relative;
    padding:0px;
}
DIV.scrollingdatagrid TABLE {
    width : 99%; /* Make room for scroll bar! */
    margin:0px;
    border:0px;
    border-collapse:separate;
   /* table-layout: fixed;*/
}
DIV.scrollingdatagrid TABLE TR .locked, DIV.scrollingdatagrid TABLE THEAD TR, DIV.scrollingdatagrid TABLE TFOOT TR {
    position:relative;
}
</style>
<script type="text/javascript"><?=$template->js?></script>
<style type="text/css"><?=$template->css?></style>

<?php

    if (!$record){
        $tmp_record = $record_previous;
    }
    else{
        $tmp_record = $record;   
    }

$post_acc = $this->user_access[$this->module_id]['post'];

$dept_div_head = false;
if (($appraiser_direct_superior['user_id'] == $this->userinfo['user_id'])) {
    $dept_div_head = true;
}
$dept_div_head_id = $appraiser_direct_superior['user_id'];

$hr = false; 
if ( $post_acc && ($appraiser->user_id != $this->userinfo['user_id']) &&  ($appraiser_direct_superior['user_id'] != $this->userinfo['user_id']) && !$personal ) {
    $readonly = "readonly='readonly'";   
    $hr = true; 
}

$rater_id = $appraiser->user_id;
$contributor_view = "";

if (!$personal) {
        $approver_id = $this->userinfo['user_id'];
    }else{
        $approver_id = $appraiser->user_id;
    }

if ($is_contributor && $appraiser->user_id != $this->userinfo['user_id']) {
  $rater_id = $this->userinfo['user_id'];
  $approver = $this->system->get_employee($rater_id);
  $contributor_view = "display:none";
}

$disable = "disabled";
$mid_disable = "disabled";

$approved_disable = false;

if( $tmp_record['employee_appraisal_status'] == 5 ){
    $approved_disable = true;
    $approved_disable_other_field = "readonly='readonly'";
}

$coach_rating_disabled = "";

$self_rating = false;
$self_rating_disabled = "";


if( $appraisee['rank_id'] >= 1 ){
    $self_rating = true;
}

if( $self_rating && ( $tmp_record['employee_appraisal_status'] == '' || $tmp_record['employee_appraisal_status'] == 1 || $tmp_record['employee_appraisal_status'] == 0) && ( $appraisee['user_id'] == $this->userinfo['user_id'] ) ){
    $self_rating_disabled = "";
}
else{
    $self_rating_disabled = "";
}

if( $self_rating && ( ( ( $tmp_record['employee_appraisal_status'] == 2 ) && ( $appraiser->user_id == $this->userinfo['user_id'] ) ) || ( $tmp_record['employee_appraisal_status'] == 9 ) && ( $appraiser_next->user_id == $this->userinfo['user_id'] ) ) ){

    $coach_rating_disabled = "";
}

if( !$self_rating && ( $tmp_record['employee_appraisal_status'] == '' || $tmp_record['employee_appraisal_status'] == 1 || $tmp_record['employee_appraisal_status'] == 0) && ( $appraiser->user_id == $this->userinfo['user_id'] ) ){

    $coach_rating_disabled = "";
}

if( $commenter && ( $tmp_record['employee_appraisal_status'] == 3 ) ){

    $coach_rating_disabled = "";

}
$rater = false;

if ($this->userinfo['user_id'] == $department_head['user_id'] && $tmp_record['employee_appraisal_status'] == 6) {
    $commenter = true;
     $coach_rating_disabled = "";
     $rater = true;
}

if ($this->userinfo['user_id'] == $division_head['user_id'] && $tmp_record['employee_appraisal_status'] == 7) {
    $commenter = true;
     $coach_rating_disabled = "";
     $rater = true;
}



if ($appraiser->user_id == $this->userinfo['user_id'] || $commenter ) {
    $rater = true;
}else{
     $approved_disable_other_field = "readonly='readonly'";
}

?>

<div class="wizard-leftcol">
  <ul>
        <?php if( !$personal ){ ?>
        <li style="width:35%" >
            <a class="leftcol-control" rel="fg-1" href="javascript:void(0)">
                <span class="wizard-ctr">1</span><br />
                <span class="wizard-label" style="width:90%">Appraisal Form</span>
            </a>
        </li>
        
       
        <li style="width:30%">
            <a class="leftcol-control" rel="fg-3" href="javascript:void(0)">
                <span class="wizard-ctr">2</span><br />
                <span class="wizard-label" style="width:90%">For Approver</span>
            </a>
        </li> 
        <?php } ?>
  </ul>
</div>

<div>
    <form id="record-form" method="post">
        <div fg_id="1" id="fg-1" class="wizard-type-form hidden  <?= ($is_contributor) ? 'wizard-last' : 'wizard-first' ;?>">
            <h4>Performance Planning and Appraisal</h4>
            <input type="hidden" name="record_id" value="<?=$record_id?>" />
            <input type="hidden" name="employee_id" id="appraisee_id" value="<?=$appraisee['user_id']?>" />
            <input type="hidden" name="appraiser_id" value="<?=$appraiser->user_id?>" />
            <input type="hidden" name="appraiser_next_id" value="<?=$appraiser_next->user_id?>" />
            <input type="hidden" name="period_id" id="period_id" value="<?=$period->planning_period_id?>" />
            <input type="hidden" id="appraisal_year" value="<?=$period->appraisal_year?>" />
            <input type="hidden" name="division_head_id" value="<?= $appraiser_direct_superior['user_id'] ?>" />
            <input type="hidden" id="appraisal_status" value="<?= $tmp_record['employee_appraisal_status'] ?>" />
             <input type="hidden" id="rater_id" value="<?=$approver_id?>" />
            <table style="width: 100%;" border="0" class="default-table boxtype" id="main">
                <tbody>
                <tr>
                    <td style="background-color: #333333;" colspan="8"><strong><span style="color: #ffffff;">EMPLOYEE INFORMATION</span></strong></td>
                </tr>   
                <tr>
                    <td style="width:75%">
                       <table style="width: 100%;" border="0" class="default-table boxtype">
                            <tbody>
                                <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>EMPLOYEE NAME:</strong></td>
                                    <td style="width: 30%;">
                                        <input type="text" name="appraisee[fullname]" value="<?=$appraisee['firstname'] . ' ' . $appraisee['lastname']?>" size="40" readonly="readonly">
                                    </td>
                                    <td style="background-color: #c0c0c0; width: 20%;" ><strong>APPRAISAL PERIOD:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisal[appraisal_period]" value="<?=date('M d, Y', strtotime($period->date_from))?> - <?=date('M d, Y', strtotime($period->date_to))?> : <?=$period->planning_period?> " id="appraisal_period" size="40" maxlength="40" readonly="readonly">
                                    </td>
                                    

                                </tr>
                                <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>RANK:</strong></td>
                                    <td style="width: 30%;">
                                        <input type="text" name="appraisee[rank]" value="<?=$appraisee['job_rank']?>" size="40" readonly="readonly">
                                        <input type="hidden" name="rank_id" value="<?=$appraisee['rank_id']?>"></td>

                                     <td style="background-color: #c0c0c0; width: 20%;"><strong>LAST APPRAISAL DATE:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="last_appraisal_date" value="" size="40" readonly="readonly"></td> <!-- ($appraisee['last_appraisal_date'] != '1970-01-01') ? date('F d, Y', strtotime($appraisee['last_appraisal_date'])) : ' ' -->
                                </tr>
                                <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>POSITION:</strong></td>
                                    <td style="width: 30%;">
                                        <input type="text" name="appraisee[position_name]" value="<?=$appraisee['position']?>" size="40" readonly="readonly">
                                        <input type="hidden" name="position_id" value="<?=$appraisee['position_id']?>"></td>
                                   
                                    
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>LAST PROMOTION DATE:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraiser_direct_superior[last_promotion]" value="" size="40" readonly="readonly"></td>
                                    
                                </tr>
                                <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>REPORTS TO:</strong></td>
                                    <td style="width: 30%;">
                                        <input type="text" name="appraiser[fullname]" value="<?=$appraiser->firstname . ' ' . $appraiser->lastname?>" size="40" readonly="readonly">
                                    </td>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>TENURE:</strong></td>
                                    <td style="width: 30%;">
                                        <input type="text" name="appraiser[tenure]" value="<?= $appraisee['tenure'] ?>" size="40" readonly="readonly">
                                    </td>
                                    
                                </tr>
                                <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>DEPARTMENT:</strong></td>
                                    <td style="width: 30%;">
                                        <input type="text" name="appraisee[department_name]" value="<?=$appraisee['department']?>" size="40" readonly="readonly">
                                        <input type="hidden" name="department_id" value="<?=$appraisee['department_id']?>" size="40" readonly="readonly"></td>
                                      
<!--                                     <td style="background-color: #c0c0c0; width: 20%;"><strong>TOTAL WEIGHTED SCORE:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisee[overall_rating]" value="<?= round($total_weighted_criteria_score) ?>" id="total_weighted_criteria_score_info" size="40" readonly="readonly"></td> -->
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>COACH RATING:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisal_rating_coach"  id="rating" value="<?php echo ($tmp_record['employee_appraisal_status'] == 5 || !$personal ? $tmp_record['coach_rating'] : '') ?>" size="40" readonly="readonly"></td>                                    
                                    <input type="hidden" name="appraisal_rating"  id="rating_coach" value="$tmp_record['coach_rating" size="40" readonly="readonly">                                    
                                </tr>
                                 <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>DEPARTMENT HEAD:</strong></td>
                                    <td style="width: 30%;">
                                        <input type="text" name="appraisee[department_name]" value="<?=$department_head['firstname'] . ' ' . $department_head['lastname']?>" size="40" readonly="readonly"></td>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>COMMITTEE RATING:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="apraisal_final_rating"  id="final_rating" value="<?php echo $tmp_record['final_rating'] ?>" <?php echo ($tmp_record['employee_appraisal_status'] <> 8 ? 'readonly="readonly"' : '') ?> size="40"></td>
                                </tr>
                                <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>DIVISION:</strong></td>
                                    <td style="width: 30%;">
                                        <input type="text" name="appraisee[department_head]" value="<?=$appraisee['division']?>" size="40" readonly="readonly">
                                        <input type="hidden" name="division_id" value="<?=$appraisee['division_id']?>"></td>
                                </tr>
                                <tr>
                                      <td style="background-color: #c0c0c0; width: 20%;"><strong>DIVISION HEAD:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisee[department_head]" value="<?=$division_head['firstname'] . ' ' . $division_head['lastname']?>" size="40" readonly="readonly"></td>
                                </tr>
                                <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>POSITION CLASSIFICATION:</strong></td>
                                    <td style="width: 30%;">
                                        <input type="text" name="appraisee[position_class]" value="<?=$appraisee['position_class']?>" size="40" readonly="readonly">
                                        <input type="hidden" name="position_class_id" value="<?=$appraisee['position_class_id']?>" size="40" readonly="readonly"></td>
                                </tr>
                            </tbody>
                        </table>                        
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding:0px;">
                        <table style="width: 100%;" border="1" class="default-table boxtype">
                            <tbody>
                                <tr>
                                    <td style="background-color: #333333;" colspan="8"><strong><span style="color: #ffffff;">Rating Scale</span></strong></td>
                                </tr>
                               <tr>

                                    <td>
                                        <table style="width: 70%;" border="0" class="default-table boxtype">
                                            <tr>
                                                <th style="width: 15%;">Qualitative Rating</th>
                                                <th style="width: 15%;">Quantitative Rating</th>
                                                <th style="width: 15%;">Overall Score</th>
                                                <th style="width: 55%;">Criteria / Standard</th>
                                            </tr>

                                            <?php foreach ($criteria_questions_options['qualitative'] as $scale_id => $scale):?>
                                            <tr>
                                                <td style="text-align:center;vertical-align:middle;border:1px solid #ddd"><?=$scale?></td>
                                                <td style="text-align:center;border:1px solid #ddd"><?=implode('<br/>',$criteria_questions_options['quantitative'][$scale_id]);?></td>
                                                <td style="text-align:center;border:1px solid #ddd"><?=implode('<br/>',$criteria_questions_options['weighted_score'][$scale_id]);?></td>
                                                <td style="vertical-align:middle;border:1px solid #ddd"><?=$criteria_questions_options['criteria_standard'][$scale_id]?></td>
                                            </tr>
                                            <?php endforeach;?>
                                        </table>
                                    </td>
                                </tr>  
                            </tbody>
                        </table> 
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding:0px" id="section">
                        <?php 
                            $ctr = 1; 
                            foreach ($form->result() as $criteria):
                                $column_count = count($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name']); 
                                if (!$criteria->is_core):?>
                                    <input type="hidden" name="criteria[]" value="<?=$criteria->employee_appraisal_criteria_id?>" />
                                    <table border="1" class="default-table boxtype valign performance_objective" ratio="<?= $criteria->ratio_weighter_score ?>">
                                        <thead>
                                            <tr>
                                                <td style="background-color: #333333;" colspan="<?= $column_count + 5?>">
                                                    <strong><span style="color: #ffffff;">Sections <?=$ctr?> - <?=$criteria->criteria_text?></span></strong>
                                                    <span style="display: inline-block; vertical-align: middle;">
                                                        <a href="javascript:void(0)" tooltip="<?=$criteria->tooltip?>" class="icon-button icon-16-info description_tooltip" atitle="description" style="background-color:transparent;border: 1px solid transparent"></a>
                                                        <!-- <label style="display:none"><?=$criteria->tooltip?></label> -->
                                                    </span>
                                                    <span style="float:right;"><a href="javascript:void(0)" class="show_hide"><span style="color: #ffffff;">Hide</span></a></span>
                                                </td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- column header of per section -->
                                            <tr>
                                                <?php 
                                                    if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id])): 
                                                        foreach ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name'] as $column_id => $column):
                                                            if (!in_array($column, array('Actual','% Achieved','% Weight Average'))):
                                                ?>
                                                                <td><b><?= $column ?></b>
                                                                    <?= ($criteria_columns[$criteria->employee_appraisal_criteria_id]['field_required'][$column_id] == 1 ? '<span class="red font-large">*</span>' : '')?>
                                                                    <?php  
                                                                    if ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_tooltip'][$column_id] != ""):?>
                                                                        <span style="display: inline-block; vertical-align: middle;">
                                                                            <a href="javascript:void(0)" tooltip="<?=$criteria_columns[$criteria->employee_appraisal_criteria_id]['column_tooltip'][$column_id]?>" class="icon-button icon-16-info description_tooltip" atitle="description" style="background-color:transparent;border: 1px solid transparent"></a>
                                                                        </span>
                                                                </td>               
                                                                <?php endif;
                                                            endif;
                                                        endforeach;
                                                    endif;                                      
                                                if( $self_rating ):?>
                                                    <td align="center"><strong>Self Rating</strong></td>
                                                <?php 
                                                    if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id])): 
                                                        foreach ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name'] as $column_id => $column):
                                                            if (in_array($column, array('% Achieved','% Weight Average'))):
                                                ?>
                                                                <td><b><?= $column ?></b>
                                                                    <?= ($criteria_columns[$criteria->employee_appraisal_criteria_id]['field_required'][$column_id] == 1 ? '<span class="red font-large">*</span>' : '')?>
                                                                    <?php  
                                                                    if ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_tooltip'][$column_id] != ""):?>
                                                                        <span style="display: inline-block; vertical-align: middle;">
                                                                            <a href="javascript:void(0)" tooltip="<?=$criteria_columns[$criteria->employee_appraisal_criteria_id]['column_tooltip'][$column_id]?>" class="icon-button icon-16-info description_tooltip" atitle="description" style="background-color:transparent;border: 1px solid transparent"></a>
                                                                        </span>
                                                                </td>               
                                                                <?php endif;
                                                            endif;
                                                        endforeach;
                                                    endif;                                                       
                                                endif; ?>
                                                    <td align="center"><strong>Coach Rating</strong></td>
                                                <?php 
                                                    if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id])): 
                                                        foreach ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name'] as $column_id => $column):
                                                            if (in_array($column, array('% Achieved','% Weight Average'))):
                                                ?>
                                                                <td><b><?= $column ?></b>
                                                                    <?= ($criteria_columns[$criteria->employee_appraisal_criteria_id]['field_required'][$column_id] == 1 ? '<span class="red font-large">*</span>' : '')?>
                                                                    <?php  
                                                                    if ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_tooltip'][$column_id] != ""):?>
                                                                        <span style="display: inline-block; vertical-align: middle;">
                                                                            <a href="javascript:void(0)" tooltip="<?=$criteria_columns[$criteria->employee_appraisal_criteria_id]['column_tooltip'][$column_id]?>" class="icon-button icon-16-info description_tooltip" atitle="description" style="background-color:transparent;border: 1px solid transparent"></a>
                                                                        </span>
                                                                </td>               
                                                                <?php endif;
                                                            endif;
                                                        endforeach;
                                                    endif;  ?>                                                    
                                            </tr>  
                                            <!-- column header of per section -->  
                                            <?php $qctr = 1; ?>
                                                <input type="hidden" class="none_core_no_question" value="<?php echo count($criteria_questions[$criteria->employee_appraisal_criteria_id]['questions']) ?>">
                                            <?php
                                                foreach($criteria_questions[$criteria->employee_appraisal_criteria_id]['questions'] as $question_id => $question):?>
                                                    <tr id="question_trq<?=$question_id?>" class="perspective" perspectiveid="<?=$question_id?>" criteria="<?php echo $criteria->employee_appraisal_criteria_id ?>" ratio-weigth="<?php echo $criteria->ratio_weighter_score ?>">
                                                        <td class="question_tdq<?=$question_id?>" width="300px" >
                                                            <span style="display: inline-block; vertical-align: middle; margin-right: 8px;"><?=$qctr++?>.&nbsp;&nbsp;<?=$question?></span><br>
                                                            <?php if ($criteria_questions[$criteria->employee_appraisal_criteria_id]['tooltip'][$question_id] != ''):?>
                                                                <span style="display: inline-block; vertical-align: middle;"><a href="javascript:void(0)" tooltip="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['tooltip'][$key]?>" class="icon-button icon-16-info description_tooltip" atitle="description"></a></span>
                                                            <?php endif; ?><br /><br />
                                                            <label>Key in Weight <span class="red font-large">*</span></label>
                                                            <input type="text" class="key_weight" perspectiveid="<?=$question_id?>"  style="width:120px;" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$question_id?>][key_weight]" value="<?= $tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$question_id][key_weight] ?>" <?= $disable ?> />
                                                        </td>
                                                        <?php if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id])): // start columns
                                                                $column_code_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['column_code'];
                                                                $column_type_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['column_type'];
                                                                $column_name_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name'];
                                                                $column_name_id = $criteria_columns[$criteria->employee_appraisal_criteria_id]['column_id'];
                                                                $appraisal_period_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['payroll_period'];
                                                                $field_required_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['field_required'];
                                                                $column_class_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['class'];

                                                                array_shift($column_code_array);
                                                                array_shift($column_type_array);
                                                                array_shift($column_name_array);
                                                                array_shift($column_name_id);
                                                                array_shift($appraisal_period_array);
                                                                array_shift($field_required_array);
                                                                array_shift($column_class_array);

                                                                $column_array_val = $tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$question_id];

                                                                if( isset($tmp_record['employee_appraisal_criteria_actual_question_array'][$criteria->employee_appraisal_criteria_id][$question_id]) ){

                                                                    $actual_column_array_val = $tmp_record['employee_appraisal_criteria_actual_question_array'][$criteria->employee_appraisal_criteria_id][$question_id];

                                                                }

                                                                foreach ($column_name_array as $column_id => $column):
                                                                    $column_class="";

                                                                    switch ($column) {
                                                                        case 'Target':
                                                                            $column_class = "target";
                                                                            break;
                                                                        case '% Weight':
                                                                            $column_class = "weight";
                                                                            break;
                                                                        case 'Actual':
                                                                            $column_class = "actual";
                                                                            break;
                                                                        case '% Achieved':
                                                                            $column_class = "achieved";
                                                                            break;                                                                    
                                                                        case '% Weight Average':
                                                                            $column_class = "weight_average";
                                                                            break; 
                                                                        case '% distribution':
                                                                            $column_class = "distribution_".$question_id;
                                                                            $percent_distribution = $column_array_val[$column_id][0];
                                                                            break;
                                                                    }

                                                                    if (!in_array($column_code_array[$column_id], array('actual','achieved','weight_average'))):
                                                                ?>
                                                                        <td class="questions_box" align="center">
                                                                            <?php 
                                                                                if( $column_code_array[$column_id] != 'actual' && $column_code_array[$column_id] != 'actual_accomplished' ): 
                                                                                    if ($column_type_array[$column_id] == 1):?>
                                                                                        <textarea <?= ($personal ? '' : 'readonly="readonly"') ?> class="<?= $column_class ?> textarea <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$question_id?>][<?=$column_id?>][]" <?= $disable ?> ><?= $column_array_val[$column_id][0]?></textarea>
                                                               
                                                                                    <?php elseif ($column_type_array[$column_id] == 2): ?>
                                                                                            <input <?= ($personal ? '' : 'readonly="readonly"') ?> type="text" style="width:80px;" class="<?= $column_class ?> <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$coblumn?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$question_id?>][<?=$column_id?>][]" value="<?= $column_array_val[$column_id][0]?>" <?= $disable ?> >
                                                                                    <?php endif;?>
                                                                                <?php else: 
                                                                                         if ($column_type_array[$column_id] == 1): ?>
                                                                                            <textarea <?= ($personal ? 'readonly="readonly"' : '') ?> class="<?= $column_class ?> textarea <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="actual[<?=$criteria->employee_appraisal_criteria_id?>][<?=$question_id?>][<?=$column_id?>][]" ><?= $actual_column_array_val[$column_id][0]?></textarea>
                                                                                   
                                                                                        <?php elseif ($column_type_array[$column_id] == 2): ?>
                                                                                            <input <?= ($personal ? 'readonly="readonly"' : '') ?> type="text" style="width:80px;" class="<?= $column_class ?> <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="actual[<?=$criteria->employee_appraisal_criteria_id?>][<?=$question_id?>][<?=$column_id?>][]" value="<?= $actual_column_array_val[$column_id][0]?>" >
                                                                                        <?php endif;?>
                                                                                <?php endif; ?>
                                                                        </td>
                                                                <?php     
                                                                    endif;  
                                                                endforeach;
                                                            endif; // end columns ?>

                                                        <?php if( $self_rating ){ ?>
                                                            <td> 
                                                                <input criteria="<?=$criteria->employee_appraisal_criteria_id?>" class="rating obj_self_rating" perspective="<?= $question_id ?>" 
                                                                    name="self_rating[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraisee['user_id']?>][<?=$question_id?>][0]" style="width:80px;" <?= $self_rating_disabled ?> percent-distribution="<?=$percent_distribution?>" value="<?= $tmp_record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$question_id][0] ?>" >

                                                                <?php $this->db->where('employee_appraisal_criteria_id',$criteria->employee_appraisal_criteria_id);
                                                                        $this->db->where('rating_scale',$selected_rating_s);
                                                                        $employee_appraisal_rating_scale_tbl = $this->db->get('employee_appraisal_rating_scale');
                                                                        $employee_appraisal_rating_scale_tbl_row = $employee_appraisal_rating_scale_tbl->row();

                                                                        $this->db->where('appraisal_scale_id',$employee_appraisal_rating_scale_tbl_row->appraisal_scale_id);
                                                                        $employee_appraisal_scale_tbl = $this->db->get('employee_appraisal_scale');
                                                                        $employee_appraisal_scale_tbl_row = $employee_appraisal_scale_tbl->row();
                                                                        echo $employee_appraisal_scale_tbl_row->appraisal_scale;
                                                                ?> 
                                                            </td>
                                                    <?php
                                                            foreach ($column_name_array as $column_id => $column):

                                                                    $column_class="";

                                                                    switch ($column) {
                                                                        case 'Target':
                                                                            $column_class = "target";
                                                                            break;
                                                                        case '% Weight':
                                                                            $column_class = "weight";
                                                                            break;
                                                                        case 'Actual':
                                                                            $column_class = "actual";
                                                                            break;
                                                                        case '% Achieved':
                                                                            $column_class = "achieved";
                                                                            break;                                                                    
                                                                        case '% Weight Average':
                                                                            $column_class = "weight_average";
                                                                            break; 
                                                                        case '% distribution':
                                                                            $column_class = "distribution_".$question_id;
                                                                            $percent_distribution = $column_array_val[$column_id][0];
                                                                            break;
                                                                    }

                                                                    if (in_array($column_code_array[$column_id], array('achieved','weight_average'))):
                                                                        $name = 'self_' . $column_code_array[$column_id];
                                                                        $column_class = 'self_' . $column_class;
                                                                ?>
                                                                        <td class="questions_box" align="center">
                                                                            <?php 
                                                                             if ($column_type_array[$column_id] == 1): ?>
                                                                                <textarea <?= $self_rating_disabled ?> readonly="readonly" class="<?= $column_class ?> textarea <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="<?php echo $name ?>[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraisee['user_id']?>][<?=$question_id?>][0]" perspective="<?= $question_id ?>"><?= $tmp_record['employee_appraisal_criteria_self_'.$column_code_array[$column_id].'_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$question_id][0] ?></textarea>
                                                                       
                                                                            <?php elseif ($column_type_array[$column_id] == 2): ?>
                                                                                <input <?= $self_rating_disabled ?> readonly="readonly" type="text" style="width:80px;" class="<?= $column_class ?> <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="<?php echo $name ?>[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraisee['user_id']?>][<?=$question_id?>][0]" value="<?= $tmp_record['employee_appraisal_criteria_self_'.$column_code_array[$column_id].'_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$question_id][0] ?>" perspective="<?= $question_id ?>">
                                                                            <?php endif;?>
                                                                        </td>
                                                                <?php     
                                                                    endif;                                                                  
                                                            endforeach;
                                                        } 
                                                    ?>
                                                        <td>
                                                            <input criteria="<?=$criteria->employee_appraisal_criteria_id?>" class="rating obj_coach_rating" perspective="<?= $question_id ?>"                                                                                                                             
                                                                    name="rating[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraiser->user_id?>][<?=$question_id?>][0]" style="width:80px;" <?= $coach_rating_disabled ?> percent-distribution="<?=$percent_distribution?>" value="<?= (!empty($tmp_record['employee_appraisal_criteria_rating_array']) ? $tmp_record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$question_id][0] : '') ?>" >
                                                            <?php 
                                                                $this->db->where('employee_appraisal_criteria_id',$criteria->employee_appraisal_criteria_id);
                                                                $this->db->where('rating_scale',$selected_rating_s);
                                                                $employee_appraisal_rating_scale_tbl = $this->db->get('employee_appraisal_rating_scale');
                                                                $employee_appraisal_rating_scale_tbl_row = $employee_appraisal_rating_scale_tbl->row();

                                                                $this->db->where('appraisal_scale_id',$employee_appraisal_rating_scale_tbl_row->appraisal_scale_id);
                                                                $employee_appraisal_scale_tbl = $this->db->get('employee_appraisal_scale');
                                                                $employee_appraisal_scale_tbl_row = $employee_appraisal_scale_tbl->row();

                                                                echo $employee_appraisal_scale_tbl_row->appraisal_scale;
                                                            ?> 
                                                        </td>

                                                        <?php
                                                            foreach ($column_name_array as $column_id => $column):
                                                                    
                                                                    $column_class="";

                                                                    switch ($column) {
                                                                        case 'Target':
                                                                            $column_class = "target";
                                                                            break;
                                                                        case '% Weight':
                                                                            $column_class = "weight";
                                                                            break;
                                                                        case 'Actual':
                                                                            $column_class = "actual";
                                                                            break;
                                                                        case '% Achieved':
                                                                            $column_class = "achieved";
                                                                            break;                                                                    
                                                                        case '% Weight Average':
                                                                            $column_class = "weight_average";
                                                                            break; 
                                                                        case '% distribution':
                                                                            $column_class = "distribution_".$question_id;
                                                                            $percent_distribution = $column_array_val[$column_id][0];
                                                                            break;
                                                                    }

                                                                    if (in_array($column_code_array[$column_id], array('achieved','weight_average'))):
                                                                        $name = 'coach_' . $column_code_array[$column_id];
                                                                        $column_class = 'coach_' . $column_class;
                                                                ?>
                                                                        <td class="questions_box" align="center">
                                                                            <?php 
                                                                             if ($column_type_array[$column_id] == 1): ?>
                                                                                <textarea <?= $coach_rating_disabled ?> readonly="readonly" class="<?= $column_class ?> textarea <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="<?php echo $name ?>[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraiser->user_id?>][<?=$question_id?>][0]" perspective="<?= $question_id ?>"><?= $tmp_record['employee_appraisal_criteria_'.$column_code_array[$column_id].'_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$question_id][0] ?></textarea>
                                                                       
                                                                            <?php elseif ($column_type_array[$column_id] == 2): ?>
                                                                                <input <?= $coach_rating_disabled ?> readonly="readonly" type="text" style="width:80px;" class="<?= $column_class ?> <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="<?php echo $name ?>[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraiser->user_id?>][<?=$question_id?>][0]" value="<?= (!empty($tmp_record['employee_appraisal_criteria_'.$column_code_array[$column_id].'_array']) ? $tmp_record['employee_appraisal_criteria_'.$column_code_array[$column_id].'_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$question_id][0] : '') ?>" perspective="<?= $question_id ?>">
                                                                            <?php endif;?>
                                                                        </td>
                                                                <?php     
                                                                    endif;                                                                  
                                                            endforeach;  
                                                        ?>                                                      
                                                    </tr>
                                                    <?php // multiple questions
                                                        $per_question_count = count($tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$question_id][0]);
                                                        $per_question_count = ($per_question_count < 1 ? 1 : $per_question_count);
                                                        $column_array_val = $tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$question_id];

                                                        
                                                                if( isset($tmp_record['employee_appraisal_criteria_actual_question_array'][$criteria->employee_appraisal_criteria_id][$question_id]) ){
                                                                    $actual_column_array_val = $tmp_record['employee_appraisal_criteria_actual_question_array'][$criteria->employee_appraisal_criteria_id][$question_id];
                                                                }
                                                                if ($per_question_count > 1){
                                                                    for ($i=1; $i < $per_question_count; $i++){ ?>
                                                                        <tr class="additional" criteria="<?php echo $criteria->employee_appraisal_criteria_id ?>" ratio-weigth="<?php echo $criteria->ratio_weighter_score ?>">
                                                                            <td align="center">&nbsp;</td>
                                                                            <?php 
                                                                            if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id])): 
                                                                                $column_code_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['column_code'];
                                                                                $column_type_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['column_type'];
                                                                                $column_name_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name'];
                                                                                $appraisal_period_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['payroll_period'];
                                                                                $field_required_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['field_required'];
                                                                                $column_class_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['class'];
                                                                                $column_name_id = $criteria_columns[$criteria->employee_appraisal_criteria_id]['column_id'];
                                                                                
                                                                                array_shift($column_code_array);
                                                                                array_shift($column_type_array);
                                                                                array_shift($column_name_array);
                                                                                array_shift($column_name_id);
                                                                                array_shift($appraisal_period_array);
                                                                                array_shift($field_required_array);
                                                                                array_shift($column_class_array);

                                                                                foreach ($column_name_array as $column_id => $column):

                                                                                    $column_class="";

                                                                                    switch ($column) {
                                                                                        case 'Target':
                                                                                            $column_class = "target";
                                                                                            break;
                                                                                        case '% Weight':
                                                                                            $column_class = "weight";
                                                                                            break;
                                                                                        case 'Actual':
                                                                                            $column_class = "actual";
                                                                                            break;
                                                                                        case '% Achieved':
                                                                                            $column_class = "achieved";
                                                                                            break;                                                                    
                                                                                        case '% Weight Average':
                                                                                            $column_class = "weight_average";
                                                                                            break; 
                                                                                        case '% distribution':
                                                                                            $column_class = "distribution_".$question_id;
                                                                                            $percent_distribution = $column_array_val[$column_id][0];
                                                                                            break;
                                                                                    }

                                                                                    if (!in_array($column_code_array[$column_id], array('actual','achieved','weight_average'))):
                                                                                    ?>
                                                                                        <td align="center">
                                                                                        <?php 
                                                                                            if( $column_code_array[$column_id] != 'actual' && $column_code_array[$column_id] != 'actual_accomplished' ):
                                                                                                    if ($column_type_array[$column_id] == 1): ?>
                                                                                                        <textarea <?= ($personal ? '' : 'readonly="readonly"') ?> class="<?= $column_class ?> textarea <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$question_id?>][<?=$column_id?>][]" <?= $disable ?> <?= $approved_disable_field ?> ><?= $column_array_val[$column_id][$i]?></textarea>
                                                                                           
                                                                                                    <?php elseif ($column_type_array[$column_id] == 2): ?>
                                                                                                        <input <?= ($personal ? '' : 'readonly="readonly"') ?> type="text" style="width:80px;" class="<?= $column_class ?> <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$question_id?>][<?=$column_id?>][]" value="<?= $column_array_val[$column_id][$i]?>" <?= $disable ?> <?= $approved_disable_field ?> >
                                                                                                    <?php endif;?>
                                                                                            <?php else: 
                                                                                                    if ($column_type_array[$column_id] == 1): ?>
                                                                                                        <textarea <?= ($personal ? 'readonly="readonly"' : '') ?> class="<?= $column_class ?> textarea <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="actual[<?=$criteria->employee_appraisal_criteria_id?>][<?=$question_id?>][<?=$column_id?>][]" <?= $approved_disable_field ?> ><?= $actual_column_array_val[$column_id][$i]?></textarea>
                                                                                                    <?php elseif ($column_type_array[$column_id] == 2): ?>
                                                                                                        <input <?= ($personal ? 'readonly="readonly"' : '') ?> type="text" style="width:80px;" class="<?= $column_class ?> <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="actual[<?=$criteria->employee_appraisal_criteria_id?>][<?=$question_id?>][<?=$column_id?>][]" value="<?= $actual_column_array_val[$column_id][$i]?>" <?= $approved_disable_field ?> >
                                                                                                    <?php endif;?>
                                                                                            <?php endif; 
                                                                                        ?>
                                                                                        </td>
                                                                                <?php  
                                                                                    endif;                                                                                      
                                                                                endforeach;
                                                                            endif;

                                                                            if( $self_rating ){?>
                                                                                <td>
                                                                                    <input criteria="<?=$criteria->employee_appraisal_criteria_id?>" class="rating obj_self_rating" perspective="<?= $question_id ?>" 
                                                                                        name="self_rating[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraisee['user_id']?>][<?=$question_id?>][<?= $i ?>]" style="width:80px;" <?= $self_rating_disabled ?> percent-distribution="<?=$percent_distribution?>" value="<?= $tmp_record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$question_id][$i] ?>" >

                                                                                    <?php 
                                                                                        $this->db->where('employee_appraisal_criteria_id',$criteria->employee_appraisal_criteria_id);
                                                                                        $this->db->where('rating_scale',$selected_rating_s);
                                                                                        $employee_appraisal_rating_scale_tbl = $this->db->get('employee_appraisal_rating_scale');
                                                                                        $employee_appraisal_rating_scale_tbl_row = $employee_appraisal_rating_scale_tbl->row();

                                                                                        $this->db->where('appraisal_scale_id',$employee_appraisal_rating_scale_tbl_row->appraisal_scale_id);
                                                                                        $employee_appraisal_scale_tbl = $this->db->get('employee_appraisal_scale');
                                                                                        $employee_appraisal_scale_tbl_row = $employee_appraisal_scale_tbl->row();
                                                                                            echo $employee_appraisal_scale_tbl_row->appraisal_scale;
                                                                                    ?> 
                                                                                </td>
                                                                            <?php
                                                                                foreach ($column_name_array as $column_id => $column):
                                                                                    $column_class="";

                                                                                    switch ($column) {
                                                                                        case 'Target':
                                                                                            $column_class = "target";
                                                                                            break;
                                                                                        case '% Weight':
                                                                                            $column_class = "weight";
                                                                                            break;
                                                                                        case 'Actual':
                                                                                            $column_class = "actual";
                                                                                            break;
                                                                                        case '% Achieved':
                                                                                            $column_class = "achieved";
                                                                                            break;                                                                    
                                                                                        case '% Weight Average':
                                                                                            $column_class = "weight_average";
                                                                                            break; 
                                                                                        case '% distribution':
                                                                                            $column_class = "distribution_".$question_id;
                                                                                            $percent_distribution = $column_array_val[$column_id][0];
                                                                                            break;
                                                                                }                                                                                    
                                                                                    if (in_array($column_code_array[$column_id], array('achieved','weight_average'))):
                                                                                        $name = 'self_' . $column_code_array[$column_id];
                                                                                        $column_class = 'self_' . $column_class;
                                                                                ?>
                                                                                        <td class="questions_box" align="center">
                                                                                            <?php 
                                                                                             if ($column_type_array[$column_id] == 1): ?>
                                                                                                <textarea <?= $self_rating_disabled ?> readonly="readonly" class="<?= $column_class ?> textarea <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="<?php echo $name ?>[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraisee['user_id']?>][<?=$question_id?>][<?php echo $i ?>]" perspective="<?= $question_id ?>"><?= $tmp_record['employee_appraisal_criteria_self_'.$column_code_array[$column_id].'_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$question_id][$i] ?></textarea>
                                                                                       
                                                                                            <?php elseif ($column_type_array[$column_id] == 2): ?>
                                                                                                <input <?= $self_rating_disabled ?> readonly="readonly" type="text" style="width:80px;" class="<?= $column_class ?> <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="<?php echo $name ?>[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraisee['user_id']?>][<?=$question_id?>][<?php echo $i ?>]" value="<?= $tmp_record['employee_appraisal_criteria_self_'.$column_code_array[$column_id].'_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$question_id][$i] ?>" perspective="<?= $question_id ?>">
                                                                                            <?php endif;?>
                                                                                        </td>
                                                                                <?php     
                                                                                    endif;                                                                  
                                                                                endforeach;                                                                                
                                                                            } ?>
                                                                            <td>
                                                                                <input criteria="<?=$criteria->employee_appraisal_criteria_id?>" class="rating obj_coach_rating" perspective="<?= $question_id ?>" 
                                                                                    name="rating[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraiser->user_id?>][<?=$question_id?>][<?= $i ?>]" style="width:80px;" <?= $coach_rating_disabled ?> percent-distribution="<?=$percent_distribution?>" value="<?= (!empty($tmp_record['employee_appraisal_criteria_rating_array']) ? $tmp_record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$question_id][$i] : '') ?>" >

                                                                                <?php 
                                                                                    $this->db->where('employee_appraisal_criteria_id',$criteria->employee_appraisal_criteria_id);
                                                                                    $this->db->where('rating_scale',$selected_rating_s);
                                                                                    $employee_appraisal_rating_scale_tbl = $this->db->get('employee_appraisal_rating_scale');
                                                                                    $employee_appraisal_rating_scale_tbl_row = $employee_appraisal_rating_scale_tbl->row();

                                                                                    $this->db->where('appraisal_scale_id',$employee_appraisal_rating_scale_tbl_row->appraisal_scale_id);
                                                                                    $employee_appraisal_scale_tbl = $this->db->get('employee_appraisal_scale');
                                                                                    $employee_appraisal_scale_tbl_row = $employee_appraisal_scale_tbl->row();

                                                                                    echo $employee_appraisal_scale_tbl_row->appraisal_scale;
                                                                                ?> 
                                                                            </td>
                                                                            <?php foreach ($column_name_array as $column_id => $column):

                                                                                    $column_class="";

                                                                                    switch ($column) {
                                                                                        case 'Target':
                                                                                            $column_class = "target";
                                                                                            break;
                                                                                        case '% Weight':
                                                                                            $column_class = "weight";
                                                                                            break;
                                                                                        case 'Actual':
                                                                                            $column_class = "actual";
                                                                                            break;
                                                                                        case '% Achieved':
                                                                                            $column_class = "achieved";
                                                                                            break;                                                                    
                                                                                        case '% Weight Average':
                                                                                            $column_class = "weight_average";
                                                                                            break; 
                                                                                        case '% distribution':
                                                                                            $column_class = "distribution_".$question_id;
                                                                                            $percent_distribution = $column_array_val[$column_id][0];
                                                                                            break;
                                                                                    }

                                                                                    if (in_array($column_code_array[$column_id], array('achieved','weight_average'))):
                                                                                        $name = 'coach_' . $column_code_array[$column_id];
                                                                                        $column_class = 'coach_' . $column_class;
                                                                                ?>
                                                                                        <td class="questions_box" align="center">
                                                                                            <?php 
                                                                                             if ($column_type_array[$column_id] == 1): ?>
                                                                                                <textarea <?= $coach_rating_disabled ?> readonly="readonly" class="<?= $column_class ?> textarea <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="<?php echo $name ?>[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraiser->user_id?>][<?=$question_id?>][<?php echo $i ?>]" perspective="<?= $question_id ?>"><?= $tmp_record['employee_appraisal_criteria_'.$column_code_array[$column_id].'_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$question_id][$i] ?></textarea>
                                                                                       
                                                                                            <?php elseif ($column_type_array[$column_id] == 2): ?>
                                                                                                <input <?= $coach_rating_disabled ?> readonly="readonly" type="text" style="width:80px;" class="<?= $column_class ?> <?= ($field_required_array[$column_id] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$question_id][$column_name_id[$column_id]]?>" name="<?php echo $name ?>[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraiser->user_id?>][<?=$question_id?>][<?php echo $i ?>]" value="<?= (!empty($tmp_record['employee_appraisal_criteria_'.$column_code_array[$column_id].'_array']) ? $tmp_record['employee_appraisal_criteria_'.$column_code_array[$column_id].'_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$question_id][$i] : '') ?>" perspective="<?= $question_id ?>">
                                                                                            <?php endif;?>
                                                                                        </td>
                                                                                <?php     
                                                                                    endif;                                                                  
                                                                            endforeach; ?>                                                                          
                                                                        </tr>  
                                                            <?php                                                
                                                                    } 
                                                                } // end multiple questions
                                                            ?>
<!--                                                     <?php if( $approvers && $approvers->num_rows() > 0 ):
                                                        foreach( $approvers->result() as $approver ):?>
                                                        <tr>
                                                            <td colspan="<?= $column_count ?>">
                                                                <table style="width:100%;">
                                                                    <tr>
                                                                        <td><div class="form-item odd ">
                                                                                <?php $mid_comment = "";
                                                                                    if( isset($tmp_record['employee_appraisal_criteria_mid_year_comments'][$criteria->employee_appraisal_criteria_id][$question_id]))
                                                                                    {
                                                                                        $mid_comment = $tmp_record['employee_appraisal_criteria_mid_year_comments'][$criteria->employee_appraisal_criteria_id][$question_id]['mid_year_comments'][$approver->user_id];
                                                                                    }
                                                                                   
                                                                                ?>
                                                                                <label>Mid-Year Performance Review Comments: (<?=$approver->firstname . ' ' . $approver->lastname?>)</label>
                                                                                <div>
                                                                                    <textarea style="width:100%;height:50px;" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$question_id?>][mid_year_comments][<?=$approver->user_id?>]" <?=(($approver->user_id == $this->userinfo['user_id']) ? $personal ? 'readonly="readonly"' : '' : 'readonly="readonly"')?> <?= $disable ?> <?= $mid_disable ?>><?= $mid_comment ?></textarea>
                                                                                </div>        
                                                                            </div>
                                                                        </td>
                                                                        <td><div class="form-item odd ">
                                                                                <label>Year-end Performance Review Comments: (<?=$approver->firstname . ' ' . $approver->lastname?>)</label>
                                                                                <div> 
                                                                                    <textarea style="width:100%;height:50px;" <?=(($approver->user_id == $this->userinfo['user_id']) ? $personal ? 'readonly="readonly"' : '' : 'readonly="readonly"')?> name="year_end_comments[<?=$criteria->employee_appraisal_criteria_id?>][<?=$question_id?>][year_end_comments][<?=$approver->user_id?>]"  <?= $coach_rating_disabled ?> ><?=($tmp_record['employee_appraisal_criteria_year_end_comments'] != "" ) ? trim($tmp_record['employee_appraisal_criteria_year_end_comments'][$criteria->employee_appraisal_criteria_id][$question_id]['year_end_comments'][$approver->user_id]) : '';?></textarea>
                                                                                </div>        
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>                                                   
                                                        </tr>
                                                     <?php endforeach;
                                                        endif;?>   -->                                                    <tr id="q<?=$question_id?>" hidden><td>&nbsp;</td></tr>
 
                                            <?php endforeach; // end question?>
                                        </tbody>
                                    </table>
                                <?php 
                                else: // if core ?>
                                    <div class="clear"></div><br>
                                    <table style="width:100%;" border="1" class="default-table boxtype valign">
                                        <thead>
                                            <tr>
                                                <td style="background-color: #333333;" colspan="7">
                                                    <strong><span style="color: #ffffff;">Section <?=$ctr?> - <?=$criteria->criteria_text?> </span></strong>
                                                    <span style="display: inline-block; vertical-align: middle;">
                                                        <a href="javascript:void(0)" tooltip="<?=$criteria->tooltip?>" class="icon-button icon-16-info description_tooltip" atitle="description" style="background-color:transparent;border: 1px solid transparent"></a>
                                                    </span>
                                                    <span style="float:right;"><a href="javascript:void(0)" class="show_hide"><span style="color: #ffffff;">Hide</span></a></span>
                                                </td>
                                            </tr>
                                        </thead> 
                                        <tbody>
                                            <?php 
                                                $competency_master_info = $this->db->get_where('appraisal_competency_master',array('appraisal_competency_master_id'=>$criteria->competency_master_id))->row();
                                                    if( $competency_master_info->competency_master_code == 'attendance' ){ ?>
                                                        <tr>
                                                            <td align="" style="width:10%;"><strong>Competencies / Values</strong>
                                                            <span style="display: inline-block; vertical-align: middle;">
                                                                <a href="javascript:void(0)" master-id="<?=$criteria->competency_master_id?>" tooltip="Click Here to view Competency Library" class="icon-button icon-16-info description_competency" atitle="description" style="background-color:transparent;border: 1px solid transparent"></a>                                                             
                                                            </span></td>
                                                            <td align="" style="width:10%;"><strong>Rating</strong></td>
                                                            <td align="" style="width:20%;"><strong>Please Refer to Employees DTR Summary</strong></td>
                                                        </tr>

                                                    <?php } 
                                                    else{ ?>
                                                        <tr>
                                                            <td align="" style="width:20%;"><strong>Competencies / Values</strong><!-- <span style="display: inline-block; vertical-align: middle;"><a href="javascript:void(0)" master-id="<?=$criteria->competency_master_id?>" tooltip="Click Here to view Competency Library" class="icon-button icon-16-info description_competency" atitle="description" style="background-color:transparent;border: 1px solid transparent"></a></span> --></td>
                                                            <?php if( $appraisee['rank_id'] >= 1){ ?>
                                                                <td align="" style="width:20%;"><strong>Self Rating</strong></td>
                                                                <td align="" style="width:20%;"><strong>Self Comment</strong></td>
                                                            <?php } ?>
                                                            <td align="" style="width:20%;"><strong>Coach Rating</strong></td>
                                                            <td align="" style="width:20%;"><strong>Coach Comment</strong></td>
                                                            <td align="center">&nbsp;</td>
                                                        </tr>
                                                    <?php }
                                                    $core_ctr = 1;
                                                    foreach ($core_values[$criteria->employee_appraisal_criteria_id] as $values){
                                                        $competencies = $this->db->get_where('appraisal_competency', array('appraisal_competency_value_id' => $values->competency_value_id));
                                                        if( $competency_master_info->competency_master_code == 'attendance' ){ ?>
                                                            <tr id="core_value<?=$values->competency_value_id?>" class="competency_value">
                                                                <td ><span style="display: inline-block; vertical-align: middle; margin-right: 8px;"><strong><?=$core_ctr?>. <?=$values->competency_value?></strong> <br /> <?=$values->competency_value_description?></td>                                       
                                                                <td align="">
                                                                    <select criteria="<?=$criteria->employee_appraisal_criteria_id?>" competency="<?= $values->competency_value_id ?>" class="rating    coach_rating" name="rating[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraiser->user_id?>][<?= $values->competency_value_id ?>][coach_rating]" style="width:100px;" <?= $coach_rating_disabled ?> >
                                                                        <option value="0" >select..</option>
                                                                        <?php $selected_rating_s= 0; 
                                                                            foreach ($rating_scale[$criteria->employee_appraisal_criteria_id]['scale'] as $r => $rate2) { ?>
                                                                                <option value="<?=$rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$r]?>" <?=($tmp_record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['coach_rating'] == $rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$r]) ? "selected='selected'" : '' ;?> ><?=$rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$r]?></option>
                                                                                <?php 
                                                                                    if($tmp_record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['coach_rating'] == $rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$r])
                                                                                    { $selected_rating_s = $rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$r]; }
                                                                            }
                                                                        ?> 
                                                                    </select>
                                                                    <?php $this->db->where('employee_appraisal_criteria_id',$criteria->employee_appraisal_criteria_id);
                                                                        $this->db->where('rating_scale',$selected_rating_s);
                                                                        $employee_appraisal_rating_scale_tbl = $this->db->get('employee_appraisal_rating_scale');
                                                                        $employee_appraisal_rating_scale_tbl_row = $employee_appraisal_rating_scale_tbl->row();

                                                                        $this->db->where('appraisal_scale_id',$employee_appraisal_rating_scale_tbl_row->appraisal_scale_id);
                                                                        $employee_appraisal_scale_tbl = $this->db->get('employee_appraisal_scale');
                                                                        $employee_appraisal_scale_tbl_row = $employee_appraisal_scale_tbl->row();

                                                                        echo $employee_appraisal_scale_tbl_row->appraisal_scale;
                                                                    ?> 
                                                                </td>
                                                                <td><textarea style="width:100%;" name="rating[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraiser->user_id?>][<?= $values->competency_value_id ?>][year_end_comment]" <?= $coach_rating_disabled ?>><?=trim($tmp_record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['year_end_comment'])?></textarea>
                                                                </td>
                                                            </tr>
                                                        <?php }
                                                        else{ ?>
                                                            <tr id="core_value<?=$values->competency_value_id?>" class="competency_value">
                                                                <td><span style="display: inline-block; vertical-align: middle; margin-right: 8px;"><strong><?=$core_ctr?>. <?=$values->competency_value?></strong> <br /> <?=$values->competency_value_description?>
                                                                </td>
                                                                <?php if( $self_rating ){ ?>
                                                                <td>
                                                                    <select criteria="<?=$criteria->employee_appraisal_criteria_id?>" competency="<?= $values->competency_value_id ?>" class="rating self_rating" name="self_rating[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraisee['employee_id']?>][<?= $values->competency_value_id ?>][self_rating]" style="width:100px;" <?= $self_rating_disabled ?>>
                                                                        <option value="0" >select..</option>
                                                                        <?php $selected_rating_s= 0; 
                                                                            foreach ($rating_scale[$criteria->employee_appraisal_criteria_id]['scale'] as $r => $rate2): ?>
                                                                                <option value="<?=$rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$r]?>" 
                                                                                <?=($tmp_record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['employee_id']][$values->competency_value_id]['self_rating'] == $rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$r]) ? "selected='selected'" : '' ;?> ><?=$rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$r]?></option>
                                                                                <?php 
                                                                                    if($tmp_record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['employee_id']][$values->competency_value_id]['self_rating'] == $rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$r]){
                                                                                        $selected_rating_s = $rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$r];
                                                                                    }
                                                                            endforeach;
                                                                        ?> 
                                                                    </select>

                                                                    <?php 
                                                                        $this->db->where('employee_appraisal_criteria_id',$criteria->employee_appraisal_criteria_id);
                                                                        $this->db->where('rating_scale',$selected_rating_s);
                                                                        $employee_appraisal_rating_scale_tbl = $this->db->get('employee_appraisal_rating_scale');
                                                                        $employee_appraisal_rating_scale_tbl_row = $employee_appraisal_rating_scale_tbl->row();

                                                                        $this->db->where('appraisal_scale_id',$employee_appraisal_rating_scale_tbl_row->appraisal_scale_id);
                                                                        $employee_appraisal_scale_tbl = $this->db->get('employee_appraisal_scale');
                                                                        $employee_appraisal_scale_tbl_row = $employee_appraisal_scale_tbl->row();

                                                                        echo $employee_appraisal_scale_tbl_row->appraisal_scale;
                                                                    ?> 
                                                                </td>                          
                                                                <td>
                                                                    <textarea  style="width:100%;" name="self_rating[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraisee['employee_id']?>][<?= $values->competency_value_id ?>][self_comment]" class="textarea" <?= $self_rating_disabled ?> ><?= $tmp_record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['employee_id']][$values->competency_value_id]['self_comment'] ?></textarea>
                                                                </td>
                                                            <?php } ?>
                                                            <td>
                                                                <select criteria="<?=$criteria->employee_appraisal_criteria_id?>" competency="<?= $values->competency_value_id ?>" class="rating coach_rating" name="rating[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraiser->user_id?>][<?= $values->competency_value_id ?>][coach_rating]" style="width:100px;" <?= $coach_rating_disabled ?> >
                                                                    <option value="0" >select..</option>
                                                                    <?php $selected_rating_s= 0; 
                                                                        foreach ($rating_scale[$criteria->employee_appraisal_criteria_id]['scale'] as $r => $rate2) { ?>
                                                                            <option value="<?=$rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$r]?>" <?=( $tmp_record['employee_appraisal_criteria_rating_array'] != "" && $tmp_record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['coach_rating'] == $rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$r]) ? "selected='selected'" : '' ;?> ><?=$rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$r]?>
                                                                            </option>
                                                                            <?php if( $tmp_record['employee_appraisal_criteria_rating_array'] != "" && $tmp_record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['coach_rating'] == $rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$r]){
                                                                                        $selected_rating_s = $rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$r];
                                                                                    }
                                                                                }
                                                                            ?> 
                                                                </select>
                                                                        <?php 
                                                                            $this->db->where('employee_appraisal_criteria_id',$criteria->employee_appraisal_criteria_id);
                                                                            $this->db->where('rating_scale',$selected_rating_s);
                                                                            $employee_appraisal_rating_scale_tbl = $this->db->get('employee_appraisal_rating_scale');
                                                                            $employee_appraisal_rating_scale_tbl_row = $employee_appraisal_rating_scale_tbl->row();

                                                                            $this->db->where('appraisal_scale_id',$employee_appraisal_rating_scale_tbl_row->appraisal_scale_id);
                                                                            $employee_appraisal_scale_tbl = $this->db->get('employee_appraisal_scale');
                                                                            $employee_appraisal_scale_tbl_row = $employee_appraisal_scale_tbl->row();

                                                                            echo $employee_appraisal_scale_tbl_row->appraisal_scale;
                                                                        ?> 
                                                            </td>  
                                                            <td align="">
                                                                <textarea  style="width:100%;" name="rating[<?=$criteria->employee_appraisal_criteria_id?>][<?=$appraiser->user_id?>][<?= $values->competency_value_id ?>][coach_comment]" class="textarea" <?= $coach_rating_disabled ?>><?=( $tmp_record['employee_appraisal_criteria_rating_array'] != "" ) ? $tmp_record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['coach_comment'] : ''?></textarea>
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                        <?php } ?>
                                                        <tr id="<?=$values->competency_value_id?>">
                                                            <td align="center" colspan="7">&nbsp;</td>
                                                        </tr>
                                                    <?php $core_ctr++; } ?>
                                            </tbody>
                                        </table>
                                <?php endif;
                                $ctr++;
                            endforeach;
                        ?>  
                    <input type="hidden" id="core_total" value="<?=array_sum($core_count)?>">
                        <table border="1" class="default-table boxtype valign" style="width:100%;">
                            <tr>
                                <td style="background-color: #333333;" colspan="8">
                                    <strong><span style="color: #ffffff;">Strengths and Areas For Improvement</span></strong>
                                </td>
                            </tr>
                        </table>
                        <table border="1" class="default-table boxtype valign">
                            <thead>
                                <tr>
                                    <td colspan="2" >  
                                        <p><strong>Specifying strengths and areas for improvement will not only guide you and your employee in clarifying steps for development of skills, but will also be a valuable input to various HRD programs. ( This also includes a feedback to Coach's own strengths and areas for improvements )</strong></p>
                                    </td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <table class="employee_strength">
                                            <thead>
                                                <tr>
                                                    <td>
                                                        <div style="float:left;">
                                                            <p><strong>1. What are the employees strengths?</strong></p> 
                                                        </div>
                                                        <?php if( !$personal && ( $rater ) && ($tmp_record['employee_appraisal_status'] != 5 ) ){ ?>
                                                        <div style="float:left; margin:0px 2px;">
                                                            <span style="display: inline-block; vertical-align: middle;">
                                                                <a tooltip="Add row" href="javascript:void(0)" class="icon-16-add icon-button add_strength_areas_improvement_row" rel="employee_strength"></a>
                                                            </span>
                                                        </div>
                                                        <?php } ?>
                                                        <div class="clear"></div>
                                                    </td>
                                                </tr>
                                            </thead>
                                            <tbody class="employee_strength">   
                                                <?php 
                                                    foreach( $tmp_record['employee_appraisal_employee_strength'] as $employee_strength_info ){
                                                        ?>
                                                            <tr class="employee_strength">
                                                                <td>
                                                                    <div style="float:left;width:307px">
                                                                        <textarea type="text" style="width: 297px; height: 58px;" name="employee_strength[]" <?=$approved_disable_other_field?> ><?= $employee_strength_info ?></textarea>
                                                                    </div>
                                                                    <?php if( !$personal && ( $rater )  && ($tmp_record['employee_appraisal_status'] != 5 )  ){ ?>
                                                                    <div style="float:left;">
                                                                        <a original-title="" tooltip="Delete row" href="javascript:void(0)" class="icon-16-delete icon-button delete_strength_areas_improvement_row"></a>
                                                                    </div>
                                                                    <?php } ?>
                                                                    <div class="clear"></div>
                                                                </td>
                                                            </tr>
                                                        <?php
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td>
                                        <table class="areas_improvement">
                                            <thead>
                                                <tr>
                                                    <td>
                                                        <div style="float:left;">
                                                            <p><strong>2. What areas of performance needs enhancement or improvement?</strong></p> 
                                                        </div>
                                                        <?php if( !$personal && ( $rater ) && ($tmp_record['employee_appraisal_status'] != 5 )  ){ ?>
                                                        <div style="float:left; margin:0px 2px;">
                                                            <span style="display: inline-block; vertical-align: middle;">
                                                                <a tooltip="Add row" href="javascript:void(0)" class="icon-16-add icon-button add_strength_areas_improvement_row" rel="areas_improvement"></a>
                                                            </span>
                                                        </div>
                                                        <?php } ?>
                                                        <div class="clear"></div>
                                                    </td>
                                                </tr>
                                            </thead>
                                            <tbody class="areas_improvement">
                                                <?php 
                                                    foreach( $tmp_record['employee_appraisal_areas_improvement'] as $areas_improvement_info ){
                                                        ?>
                                                            <tr class="areas_improvement">
                                                                <td>
                                                                    <div style="float:left;width:307px">
                                                                        <textarea type="text" name="areas_improvement[]" style="width: 297px; height: 58px;" <?=$approved_disable_other_field?>   ><?= $areas_improvement_info ?></textarea>
                                                                    </div>
                                                                    <?php if( !$personal && ( $rater )  && ($tmp_record['employee_appraisal_status'] != 5 )  ){ ?>
                                                                    <div style="float:left;">
                                                                        <a original-title="" tooltip="Delete row" href="javascript:void(0)" class="icon-16-delete icon-button delete_strength_areas_improvement_row"></a>
                                                                    </div>
                                                                    <?php } ?>
                                                                    
                                                                    <div class="clear"></div>
                                                                </td>
                                                            </tr>
                                                        <?php
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </td>
                            </tr>
                            </tbody>
                        </table>

                        <!-- </div> -->
                    </td>
                </tr>
                </tbody>
            </table>


    <table style="width: 100%;" border="0" class="default-table boxtype valign">
        <tr>
            <td style="background-color: #333333;" colspan="8">
                <strong><span style="color: #ffffff;">OVERALL RATING</span></strong>
            </td>
        </tr>
    </table>
     <table style="width: 100%;" border="0" class="default-table boxtype valign">
                <thead>
                    <tr>
                        <td><strong>GENERAL CRITERIA</strong></td>
                        <td><strong>KEY IN WEIGHT</strong></td>
                        <td><strong>SELF RATING</strong></td>
                        <td><strong>COACH'S RATING</strong></td>
                        <td><strong>TOTAL WEIGHTED / AVERAGE</strong></td>
                        <td><strong>COACH'S SECTION RATING</strong></td>
                        <td><strong>WEIGH IN (%)</strong></td>
                        <td><strong>TOTAL WEIGHTED SCORE</strong></td>
                        <td><strong>COACH'S TOTAL WEIGHTED SCORE</strong></td>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_weighted_criteria_score = array();
                    $total_weighted_self_rate_score = array();
                    $total_weight = 0;

                    $self_rate_cnt = 0;
                    foreach ($form->result() as $key_criteria => $criteria):
                        if(!$criteria->is_core):
                            foreach($criteria_questions[$criteria->employee_appraisal_criteria_id]['questions'] as $key => $question):
                                foreach ($tmp_record['employee_appraisal_criteria_self_weight_average_array'][$criteria->employee_appraisal_criteria_id] as $key5 => $value5) {
                                    if (!empty($value5[$key]))
                                        $total_self_rate_core[$criteria->employee_appraisal_criteria_id][] = array_sum($value5[$key]);
                                }
                                foreach ($tmp_record['employee_appraisal_criteria_weight_average_array'][$criteria->employee_appraisal_criteria_id] as $key5 => $value5) {
                                    if (!empty($value5[$key]))
                                        $total_coach_rate_core[$criteria->employee_appraisal_criteria_id][] = array_sum($value5[$key]);
                                }                                

                                $weight = ( $tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key]['key_weight'] != 0 )? $tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key]['key_weight'] : 0 ; 
                                if ($weight > 0) {
                                    $total_weight += $weight;
                                    $self_rate_cnt++;
                                }
                            endforeach;

                            $total_section_rate_init[$key_criteria] = (array_sum($total_self_rate_core[$criteria->employee_appraisal_criteria_id]) / $total_weight) * 100;
                            $total_weighted_score_init[$key_criteria] = number_format(get_in_range((array_sum($total_self_rate_core[$criteria->employee_appraisal_criteria_id]) / $total_weight) * 100) * $criteria->ratio_weighter_score / 100,2,'.','');

                            $total_coach_section_rate_init[$key_criteria] = (array_sum($total_coach_rate_core[$criteria->employee_appraisal_criteria_id]) / $total_weight) * 100;
                            $total_coach_weighted_score_init[$key_criteria] = number_format(get_in_range((array_sum($total_coach_rate_core[$criteria->employee_appraisal_criteria_id]) / $total_weight) * 100) * $criteria->ratio_weighter_score / 100,2,'.','');
                        else:
                            foreach ($core_values[$criteria->employee_appraisal_criteria_id] as $key => $values):
                                if (!empty($tmp_record['employee_appraisal_criteria_self_rating_array'])){
                                    $total_self_rate_core[$criteria->employee_appraisal_criteria_id][] = number_format($tmp_record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$values->competency_value_id]['self_rating'],2,'.','');
                                }
                                if (!empty($tmp_record['employee_appraisal_criteria_rating_array'])){
                                    $total_coach_rate_core[$criteria->employee_appraisal_criteria_id][] = number_format($tmp_record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['coach_rating'],2,'.','');
                                }
                            endforeach;
                            $self_rate_cnt = count($total_self_rate_core[$criteria->employee_appraisal_criteria_id]);                            
                            
                            $total_section_rate_init[$key_criteria] = number_format((array_sum($total_self_rate_core[$criteria->employee_appraisal_criteria_id]) / $self_rate_cnt),2,'.','');
                            $total_weighted_score_init[$key_criteria] = number_format(((array_sum($total_self_rate_core[$criteria->employee_appraisal_criteria_id]) / $self_rate_cnt) * $criteria->ratio_weighter_score) / 100,2,'.','' );

                            $total_coach_section_rate_init[$key_criteria] = number_format((array_sum($total_coach_rate_core[$criteria->employee_appraisal_criteria_id]) / $self_rate_cnt),2,'.','');
                            $total_coach_weighted_score_init[$key_criteria] = number_format(((array_sum($total_coach_rate_core[$criteria->employee_appraisal_criteria_id]) / $self_rate_cnt) * $criteria->ratio_weighter_score) / 100,2,'.','' );                                                                                
                        endif;
                    endforeach;

                    $total_self_rate_core_org = $total_self_rate_core;

                    foreach ($form->result() as $key_criteria => $criteria):
                        $total_self_rate = array(); 
                        $total_self_rate_core = array(); 
                        $total_self_rate_criteria = array();
                    ?>
                        <input type="hidden" id="inp_section_rating<?=$criteria->employee_appraisal_criteria_id?>" name="section_rating[<?=$criteria->employee_appraisal_criteria_id?>]" value="<?=$tmp_record['employee_appraisal_criteria_question_sec_rating_array'][$criteria->employee_appraisal_criteria_id]?>">
                        
                    <tr>
                        <td><strong><?= $criteria->criteria_text ?></strong></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td id="section_rating_<?=$criteria->employee_appraisal_criteria_id?>"><?= number_format($total_section_rate_init[$key_criteria],2,'.','') ?></td>
                        <td id="coach_rating_<?=$criteria->employee_appraisal_criteria_id?>"><?= number_format($total_coach_section_rate_init[$key_criteria],2,'.','') ?></td>
                        <td class="weighter_score_<?= $criteria->employee_appraisal_criteria_id ?>"><?= $criteria->ratio_weighter_score ?></td>
                        <td id="total_weighted_<?= $criteria->employee_appraisal_criteria_id ?>" weighter-score="<?= $criteria->ratio_weighter_score ?>" class="weighter_score"> <?php
                                echo $total_weighted_score_init[$key_criteria];
                                $total_weighted_criteria_score[] = number_format($tmp_record['employee_appraisal_criteria_question_sec_rating_array'][$criteria->employee_appraisal_criteria_id] * $criteria->ratio_weighter_score ,2,'.','') ?> </td>
                        <td id="coach_total_weighted_<?= $criteria->employee_appraisal_criteria_id ?>" weighter-score="<?= $criteria->ratio_weighter_score ?>" class="weighter_score"> <?php
                                echo $total_coach_weighted_score_init[$key_criteria];
                                $total_weighted_criteria_score[] = number_format($tmp_record['employee_appraisal_criteria_question_sec_rating_array'][$criteria->employee_appraisal_criteria_id] * $criteria->ratio_weighter_score ,2,'.','') ?> </td>
                        <?php
                            switch ($criteria->employee_appraisal_criteria_id) {
                                //KRA
                                case 4:
                        ?>
                                    <input type="hidden" name="employee_appraisal_self_rating_kra" value="<?=$total_weighted_score_init[$key_criteria]?>">
                                    <input type="hidden" name="employee_appraisal_coach_rating_kra" value="<?=$total_coach_weighted_score_init[$key_criteria]?>">
                        <?php
                                    break;
                                //LEADERSHIP
                                case 5:
                        ?>
                                    <input type="hidden" name="employee_appraisal_self_rating_leadership" value="<?=$total_weighted_score_init[$key_criteria]?>">
                                    <input type="hidden" name="employee_appraisal_coach_rating_leadership" value="<?=$total_coach_weighted_score_init[$key_criteria]?>">                        
                        <?php
                                    break;
                                //VALUES
                                case 6:
                        ?>
                                    <input type="hidden" name="employee_appraisal_self_rating_values" value="<?=$total_weighted_score_init[$key_criteria]?>">
                                    <input type="hidden" name="employee_appraisal_coach_rating_values" value="<?=$total_coach_weighted_score_init[$key_criteria]?>">                        
                        <?php
                                    break;
                                //COMPETENCIES
                                case 7:
                                case 13:
                        ?>
                                    <input type="hidden" name="employee_appraisal_self_rating_competencies" value="<?=$total_weighted_score_init[$key_criteria]?>">
                                    <input type="hidden" name="employee_appraisal_coach_rating_competencies" value="<?=$total_coach_weighted_score_init[$key_criteria]?>">                        
                        <?php                                
                                    break;                                                                        
                            }
                        ?>
                    </tr>
                        <?php if(!$criteria->is_core):
                                
                                foreach($criteria_questions[$criteria->employee_appraisal_criteria_id]['questions'] as $key => $question):

                                    $weight = ( $tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key]['key_weight'] != 0 )? $tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key]['key_weight'] : 0 ; 
                                        $total_self_rate[] = $tmp_record['employee_appraisal_criteria_self_rating_weight_array'][$key];
                                        $total_self_rate_criteria[$criteria->employee_appraisal_criteria_id][] = $tmp_record['employee_appraisal_criteria_self_rating_weight_array'][$key];

                                ?>
                                    <tr>
                                        <td><?= $question ?>
                                            <input type="hidden" id="coach_total_rating_<?=$key?>" name="rating_weight[<?=$key?>]" value="<?=$total_coach_rate_core[$criteria->employee_appraisal_criteria_id][$key - 1]?>" class="coach_rating_field_<?=$criteria->employee_appraisal_criteria_id?> coach_total_weight_average">
                                            <input type="hidden" id="self_total_rating_<?=$key?>" name="self_rating_weight[<?=$key?>]" value="<?=$total_self_rate_core_org[$criteria->employee_appraisal_criteria_id][$key - 1]?>" class="self_rating_field_<?=$criteria->employee_appraisal_criteria_id?> self_total_weight_average">
                                            <input type="hidden" id="self_weighted_score_<?=$key?>" class="self_weighted_score" name="self_weighted_score[<?=$key?>]" value="<?php echo $tmp_record['employee_appraisal_criteria_self_weighted_score_array'][$key] ?>">
                                            <input type="hidden" id="coach_weighted_score_<?=$key?>" class="coach_weighted_score" name="coach_weighted_score[<?=$key?>]" value="<?php echo $tmp_record['employee_appraisal_criteria_weighted_score_array'][$key] ?>">
                                        </td>
                                        <td class="perspective_weight_<?=$key?>"><?=$weight?></td>
                                        <td id="non_core_self_rating_<?=$key?>" class="self_rating_field"><?=($tmp_record['employee_appraisal_criteria_self_rating_weight_array'][$key] > 0 ? number_format($tmp_record['employee_appraisal_criteria_self_rating_weight_array'][$key],2,'.','') : 0)?></td>
                                        <td id="non_core_coach_rating_<?= $key ?>" class="coach_rating_field"><?=($tmp_record['employee_appraisal_criteria_rating_weight_array'][$key] > 0 ? number_format($tmp_record['employee_appraisal_criteria_rating_weight_array'][$key],2,'.','') : 0)?></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                        <?php   endforeach; ?>
                                <input type="hidden" id="self_total_weighted<?=$criteria->employee_appraisal_criteria_id?>" class="self_total_weighted" weighter-score="<?=$criteria->ratio_weighter_score?>" value="<?=$total_weighted_score_init[$key_criteria] * 100?>">
                                <input type="hidden" id="coach_total_weighted<?=$criteria->employee_appraisal_criteria_id?>" class="coach_total_weighted" weighter-score="<?=$criteria->ratio_weighter_score?>" value="<?=$total_coach_weighted_score_init[$key_criteria] * 100?>">                        
                        <?php else:
                                foreach ($core_values[$criteria->employee_appraisal_criteria_id] as $values):?>  
                                <tr>
                                    <td><?=$values->competency_value?></td>
                                    <td></td>
                                    <td class="core_self_rating_<?= $values->competency_value_id ?> core_rating_self_<?=$criteria->employee_appraisal_criteria_id?>">
                                        <?php if ($self_rating) {
                                            if($tmp_record['employee_appraisal_criteria_self_rating_array'] != "" ){
                                                echo number_format($tmp_record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$values->competency_value_id]['self_rating'],2,'.','');
                                                $total_self_rate_core[$criteria->employee_appraisal_criteria_id][] = number_format($tmp_record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$values->competency_value_id]['self_rating'],2,'.','');

                                            }
                                        }?>
                                    </td>
                                    <td class="core_coach_rating_<?= $values->competency_value_id ?> core_rating_<?=$criteria->employee_appraisal_criteria_id?>" >
                                        <?=($tmp_record['employee_appraisal_criteria_rating_array']) ? $tmp_record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['coach_rating'] : ''?>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                        <?php   endforeach;
                                
                                $self_rate_cnt = count($total_self_rate_core[$criteria->employee_appraisal_criteria_id]);
                                $total_self_rate_criteria[$criteria->employee_appraisal_criteria_id][] = array_sum($total_self_rate_core[$criteria->employee_appraisal_criteria_id]) / $self_rate_cnt;
                                $total_self_rate[] = array_sum($total_self_rate_core[$criteria->employee_appraisal_criteria_id]) / $self_rate_cnt;
                        ?>
                                <input type="hidden" id="self_total_weighted<?=$criteria->employee_appraisal_criteria_id?>" class="self_total_weighted" weighter-score="<?=$criteria->ratio_weighter_score?>" value="<?=$total_section_rate_init[$key_criteria] * $criteria->ratio_weighter_score?>">
                                <input type="hidden" id="coach_total_weighted<?=$criteria->employee_appraisal_criteria_id?>" class="coach_total_weighted" weighter-score="<?=$criteria->ratio_weighter_score?>" value="<?=$total_coach_section_rate_init[$key_criteria] * $criteria->ratio_weighter_score?>">
                        <?php
                              endif;

                              /*$total_weighted_score = (number_format(array_sum($total_self_rate_criteria[$criteria->employee_appraisal_criteria_id]),2,'.','' ) * $criteria->ratio_weighter_score) / 100;*/
                              ?>

                            <input type="hidden" id="self_section_rating<?=$criteria->employee_appraisal_criteria_id?>" value="<?=number_format(array_sum($total_self_rate_criteria[$criteria->employee_appraisal_criteria_id]),2,'.','' )?>">
                            <!-- <input type="hidden" id="self_weighted_total_score<?=$criteria->employee_appraisal_criteria_id?>" value="<?=$total_weighted_score?>"> -->
                    <?php 
          
                    //$total_weighted_self_rate_score[] = number_format(array_sum($total_self_rate),2,'.','' )  * $criteria->ratio_weighter_score;
                    endforeach;
                     
                    ?>
                    <tr>
                        <td><strong>Total Weighted Score</strong></td>
                        <td>100%</td>
                        <td id="total_self_rating"><div style="visibility: hidden;"><?= number_format(( $total_self_rating > 0 )? $total_self_rating : '',2,'.','') ?></div></td>
                        <td id="total_coach_rating"><div style="visibility: hidden;"><?= number_format($total_coach_rating,2,'.','') ?></div></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><strong>Coach Rating</strong></td>
                        <td id="coach_total_weighted_criteria_score_s">
                            <?php 
                                if ($tmp_record['employee_appraisal_status'] == 8 || !$personal) {
                                    $total_weighted_criteria_score = array_sum($total_coach_weighted_score_init);
                                    echo number_format($total_weighted_criteria_score,2,'.','');
                                }
                            ?>
                        </td>
                    </tr>

                    <?php if($self_rating){ ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><strong>Self Rating</strong></td>
                        <td id="total_weighted_criteria_score_s">
                            <?php $total_weighted_self_rate_score = array_sum($total_weighted_score_init);
                               echo number_format(($total_weighted_self_rate_score),2,'.','') ?></td>
                    </tr>
                    <?php } ?>
                </tbody>


            </table>



    <table style="width: 100%;" border="0" class="default-table boxtype valign">
        <tbody>
                 <tr>
                    <td align="center" colspan="4">RATEE'S COMMENTS<span class="red font-large">*</span></td>
                </tr>
                <tr>
                    <td align="center" colspan="3"><textarea <?= ( !$personal  ? 'readonly="readonly"' : '') ?> class="required" cname="RATEE'S COMMENTS" style="width:100%" name="employee_appraisal_or_ratees_remarks" id="employee_appraisal_or_ratees_remarks" placeholder=""><?=$tmp_record['employee_appraisal_or_ratees_remarks']?></textarea></td>
                    <td align="left">
                        <p>Remarks<span class="red font-large">*</span></p>
                    </td>
                </tr>                  
<!--                 <tr>
                    <td align="center" colspan="3"><textarea <?= ( ( !$personal || ( $personal && $tmp_record['employee_appraisal_status'] != 4 )  ) ? 'readonly="readonly"' : '') ?> class="required" cname="RATEE'S COMMENTS" style="width:100%" name="employee_appraisal_or_ratees_comments" id="employee_appraisal_or_ratees_comments" placeholder="<?= $tmp_record['employee_appraisal_status'] ?>What's on your mind? Is there anything you would like your coach and other superiors to know with regard to your current and future performance?"><?=$tmp_record['employee_appraisal_or_ratees_comments']?></textarea></td>
                    <td align="left">
                        <p>GENERAL COMMENTS<span class="red font-large">*</span></p>
                        <?php if ($record_id == -1) {
                            $tmp_record['employee_appraisal_or_gen_comments'] = 1;
                        }?>

                         <p><?= form_radio('employee_appraisal_or_gen_comments',1,($tmp_record['employee_appraisal_or_gen_comments'] == 1 ? TRUE : FALSE),( ( !$personal || ( $personal && $tmp_record['employee_appraisal_status'] != 4 ) ) ? "onclick='javascript: return false;'" : ""))?> I agree with the rating</p>
                        <p><?= form_radio('employee_appraisal_or_gen_comments',0,($tmp_record['employee_appraisal_or_gen_comments'] == 0 ? TRUE : FALSE),( ( !$personal || ( $personal && $tmp_record['employee_appraisal_status'] != 4 ) ) ? "onclick='javascript: return false;'" : ""))?> I disagree with the rating</p>
                    </td>
                </tr>  -->
                <tr>
                    <td align="center" colspan="4">COACH / RATER'S COMMENTS (to be accomplished only after the PA Discussion)</td>
                </tr>
                <tr>
                    <td align="center" colspan="4"><textarea <?=(($appraiser->user_id == $this->userinfo['user_id']) ? $personal ? 'readonly="readonly"' : '' : 'readonly="readonly"')?>  class="required" cname="COACH / RATER'S COMMENTS" style="width:100%" id="employee_appraisal_or_raters_comments" placeholder="What's on your mind? Is there anything you would like your team members and  superiors to know  with regard to <?=$appraisee['firstname'] . ' ' . $appraisee['lastname']?> current and future performance?" name="employee_appraisal_or_raters_comments[]"><?=$tmp_record['employee_appraisal_or_raters_comments'][0]?></textarea></td>
                </tr> 
        </tbody>
    </table>
    <br/>  
        <table style="width: 100%;" border="0" class="default-table boxtype">
            <tbody>   
                <tr>
                    <td>
                        <input readonly="readonly" type="text" style="width:100%" value="<?=$appraisee['firstname'] . ' ' . $appraisee['lastname']?>">
                        <p>RATEE'S SIGNATURE OVER PRINTED NAME</p>
                    </td>
                    <td>
                        
                        <input <?= (!$personal ? 'readonly="readonly"' : '') ?> type="text" cname="RATING RATEE'S SIGN DATE" style="width:92%"  class="input-text <?= (!$personal ? '' : 'date') ?> required" name="employee_appraisal_or_rates_sign_date" value="<?=(($tmp_record['employee_appraisal_or_rates_sign_date'] != '' && $tmp_record['employee_appraisal_or_rates_sign_date'] != '0000-00-00' ) ? date('m/d/Y',strtotime($tmp_record['employee_appraisal_or_rates_sign_date'])) : '')?>">
                        <p>DATE<span class="red font-large">*</span></p>
                    </td>
                    
                </tr>      
                <tr><td colspan="2"></td></tr> 
                <tr><td colspan="2"></td></tr>                                                   
                <tr>
                    <td>                        
                        <input readonly="readonly" type="text" style="width:100%" value="<?=$appraiser->firstname . ' ' . $appraiser->lastname?>">
                        <p>COACH / RATER'S SIGNATURE OVER PRINTED NAME</p>
                    </td>
                    <td>
                        
                        <input <?= (!$personal && ($appraiser->user_id == $this->userinfo['user_id']) ? '' : 'readonly="readonly"') ?> type="text" cname="RATING RATER'S SIGN DATE" style="width:92%" class="input-text <?= (!$personal && ($appraiser->user_id == $this->userinfo['user_id'])? 'date' : '') ?> required" name="employee_appraisal_or_raters_sign_date[<?=$appraiser->user_id?>]" value="<?=(($tmp_record['employee_appraisal_or_raters_sign_date'][$appraiser->user_id] != '' && $tmp_record['employee_appraisal_or_raters_sign_date'][$appraiser->user_id] != '0000-00-00') ? date('m/d/Y',strtotime($tmp_record['employee_appraisal_or_raters_sign_date'][$appraiser->user_id])) : '')?>" id="employee_appraisal_or_raters_sign_date<?=$appraiser->user_id?>">
                       <p>DATE<span class="red font-large">*</span></p>
                    </td>
                </tr> 
                <tr><td colspan="2"></td></tr> 
                <tr><td colspan="2"></td></tr> 
                <?php if($approvers  && $approvers->num_rows()):
                        foreach ($approvers->result() as $approver):
                            if($appraiser->user_id != $approver->user_id):?>
                            <tr>
                                <td>
                                    <input readonly="readonly" type="text" style="width:100%" value="<?=$approver->firstname . ' ' . $approver->lastname?>">
                                    <p>COACH / RATER'S SIGNATURE OVER PRINTED NAME</p>
                                </td>
                                <td>
                                    <input <?= (!$personal && ($approver->user_id == $this->userinfo['user_id']) ? '' : 'readonly="readonly"') ?> type="text" cname="RATING RATER'S SIGN DATE" style="width:92%" class="input-text <?= (!$personal && ($approver->user_id == $this->userinfo['user_id'])? 'date' : '') ?> required" name="employee_appraisal_or_raters_sign_date[<?=$approver->user_id?>]" value="<?=(($tmp_record['employee_appraisal_or_raters_sign_date'][$approver->user_id] != '' && $tmp_record['employee_appraisal_or_raters_sign_date'][$approver->user_id] != '0000-00-00') ? date('m/d/Y',strtotime($tmp_record['employee_appraisal_or_raters_sign_date'][$approver->user_id])) : '')?>" id="employee_appraisal_or_raters_sign_date<?=$approver->user_id?>" >
                                    <p>DATE<span class="red font-large">*</span></p>
                                </td>
                            </tr> 
                            <tr><td colspan="2"></td></tr> 
                            <tr><td colspan="2"></td></tr>
                <?php       endif;
                        endforeach;
                       endif;?>                       
            </tbody>
        </table>
        <br />
    <div class="clear"></div>
    <br/>
    <?php if(!$personal ){ ?>
        <div class="page-navigator align-right">
            <div class="btn-prev-disabled"> <a href="javascript:void(0)"><span>Prev</span></a></div>
            <div class="btn-prev hidden"> <a onclick="prev_wizard()" href="javascript:void(0)" class="tipsy-autons"><span>Prev</span></a></div>
            <div class="btn-next"> <a onclick="next_wizard()" href="javascript:void(0)" class="tipsy-autons"><span>Next</span></a></div>
            <div class="btn-next-disabled hidden"> <a href="javascript:void(0)"><span>Next</span></a></div>
        </div>
    <?php } ?>
        </div>


<div fg_id="3" id="fg-3" class="wizard-type-form hidden">
    <table style="width: 100%;" border="0" class="default-table boxtype">
        <tbody>  
            <tr>
                <td <?=$display?> align="center" colspan="4">COMMENTS AND RECOMMENDATION OF IMMEDIATE SUPERIOR (<?=$appraiser->firstname . ' ' . $appraiser->lastname?>) <span class="red font-large">*</span></td>
            </tr>  
            <tr>
                <td <?=$display?> align="center" colspan="4"><textarea <?=(($appraiser->user_id == $this->userinfo['user_id']) ? $personal ? 'readonly="readonly"' : '' : 'readonly="readonly"')?> class="required" cname="COACH / RATER'S COMMENTS" style="width:100%" id="comments_recommendation_rater" name="employee_appraisal_or_raters_comments[]"><?=$tmp_record['employee_appraisal_or_raters_comments'][1]?></textarea></td>
            </tr> 

            <?php if( $approvers && $approvers->num_rows() > 0 ):
                    foreach( $approvers->result() as $approver_info ):
                        if( $approver_info->approver != $appraiser->user_id  ){ ?>
                            <tr>
                                <td <?=$display?> align="center" colspan="4">COMMENTS AND RECOMMENDATION (<?=$approver_info->firstname . ' ' . $approver_info->lastname?>) <span class="red font-large">*</span></td>
                            </tr>
                            <tr>
                                <td <?=$display?> align="center" colspan="4">
                                    <textarea <?=(($approver_info->user_id == $this->userinfo['user_id']) ? $personal ? 'readonly="readonly"' : '' : 'readonly="readonly"')?> class="required" cname="COACH / RATER'S COMMENTS" style="width:100%" id="comments_recommendation_rater" name="employee_appraisal_raters_comments[<?=  $approver_info->user_id ?>]"><?=$tmp_record['employee_appraisal_raters_comments'][$approver_info->user_id]?></textarea></td>
                            </tr> 
                        <?php } 
                    endforeach;
                endif; ?>
            <tr>
                <td style="" align="center" colspan="4">COMMENTS AND RECOMMENDATION OF Division Head (<?=$division_head['firstname'] . ' ' . $division_head['lastname']?>) <span class="red font-large">*</span></td>
            </tr>  
            <tr>
                <td <?=$display?> align="center" colspan="4">
                    <textarea <?=(($division_head['user_id'] == $this->userinfo['user_id']) ? $personal ? 'readonly="readonly"' : '' : 'readonly="readonly"')?> class="required" 
                                cname="COMMENTS AND RECOMMENDATION OF Division Head" style="width:100%" id="comments_recommendation_division_head" name="final_approval_remarks"><?=$tmp_record['final_approval_remarks']?></textarea>
                </td>
            </tr> 
        </tbody>
    </table>
    <br /><div class="clear"></div><br /><div class="clear"></div><br /><div class="clear"></div>

    <?php if( !$personal ){ ?>

        <div class="page-navigator align-right">
            <div class="btn-prev-disabled"> <a href="javascript:void(0)"><span>Prev</span></a></div>
            <div class="btn-prev hidden"> <a onclick="prev_wizard()" href="javascript:void(0)" class="tipsy-autons"><span>Prev</span></a></div>
            <div class="btn-next"> <a onclick="next_wizard()" href="javascript:void(0)" class="tipsy-autons"><span>Next</span></a></div>
            <div class="btn-next-disabled hidden"> <a href="javascript:void(0)"><span>Next</span></a></div>
        </div>

    <?php } ?>

</div>
    </form>
</div>

 <div class="clear"></div>

<div class="form-submit-btn <?=($personal) ? '' : 'hidden' ;?>">
        <div class="icon-label-group">
            <span>
                
                <?php 

                    

                    if( $self_rating ){

                        //Draft or New
                        if( ( $tmp_record['employee_appraisal_status'] == "" || $tmp_record['employee_appraisal_status'] == 1 || $tmp_record['employee_appraisal_status'] == 0) && ( $appraisee['user_id'] == $this->userinfo['user_id'] ) ){ ?>

                            <div class="icon-label"> 
                                <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 1)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
                                    <span>Save as Draft</span> 
                                </a> 
                            </div>
                            <div class="icon-label"> 
                                <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 2)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> 
                                    <span>Send to Rater</span> 
                                </a> 
                            </div>

                        <?php
                        }

                        //For Rater
                        if( $tmp_record['employee_appraisal_status'] == 2 && ( $appraiser->user_id == $this->userinfo['user_id'] ) ){
                            if (empty($appraiser_next)) {
                                if ($appraisee['rank_id'] > 1) {
                                    $appraisal_status = 7;
                                }else{
                                    $appraisal_status = 6;
                                }
                            }
                            else {
                                $appraisal_status = 9;
                            }
                         ?>
                            <div class="icon-label"> 
                                <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 2)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
                                    <span>Save and Back</span> 
                                </a> 
                            </div>
                            <div class="icon-label"> 
                                <a onclick="ajax_save('conformed', <?php echo $show_wizard_control ? 1 : 0 ?>, '', <?=$appraisal_status?>)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> 
                                    <span>Save and Send</span> 
                                </a> 
                            </div>                            
                        <?php }
                            
                        //For Next Rater
                        if( $tmp_record['employee_appraisal_status'] == 9 && ( $appraiser_next->user_id == $this->userinfo['user_id'] ) ){
                            if ($appraisee['rank_id'] > 1) {
                                $appraisal_status = 7;
                            }else{
                                $appraisal_status = 6;
                            }
                         ?>
                            <div class="icon-label"> 
                                <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 9)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
                                    <span>Save and Back</span> 
                                </a> 
                            </div>
                            <div class="icon-label"> 
                                <a onclick="ajax_save('conformed', <?php echo $show_wizard_control ? 1 : 0 ?>, '', <?=$appraisal_status?>)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> 
                                    <span>Save and Send</span> 
                                </a> 
                            </div>                            
                        <?php }

                        //For Commenter ( person that set in employee approver )
                        if( $tmp_record['employee_appraisal_status'] == 3 && $commenter ){

                            $label = ( $last_commenter )? 'Conforme' : 'Endorse'; ?>

                            <div class="icon-label"> 
                                <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 3)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
                                    <span>Save and Back</span> 
                                </a> 
                            </div>
                            <div class="icon-label"> 
                                <a onclick="ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 4)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> 
                                    <span><?= $label ?></span> 
                                </a> 
                            </div>

                        <?php }

                        //Conforme
                        if( $tmp_record['employee_appraisal_status'] == 4 && ( $appraisee['user_id'] == $this->userinfo['user_id'] ) ){
                                if ($appraisee['rank_id'] > 1) {
                                    $appraisal_status = 7;
                                }else{
                                    $appraisal_status = 6;
                                }
                            ?>

                            <div class="icon-label"> 
                                <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 4)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
                                    <span>Save and Back</span> 
                                </a> 
                            </div>
                            <div class="icon-label"> 
                                <a onclick="ajax_save('conformed', <?php echo $show_wizard_control ? 1 : 0 ?>, '', <?=$appraisal_status?>)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> 
                                    <span>Save and Send</span> 
                                </a> 
                            </div>

                        <?php
                                }elseif ($tmp_record['employee_appraisal_status'] == 6 &&  $department_head['user_id'] == $this->userinfo['user_id']) {?>
                                    
                                    <div class="icon-label"> 
                                        <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 6)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
                                            <span>Save and Back</span> 
                                        </a> 
                                    </div>
                                    <div class="icon-label"> 
                                        <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 7)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> 
                                            <span>Endorse</span> 
                                        </a> 
                                    </div>

                               <?php }elseif ($tmp_record['employee_appraisal_status'] == 7 &&  $division_head['user_id'] == $this->userinfo['user_id']) {?>
                                    
                                    <div class="icon-label"> 
                                        <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 7)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
                                            <span>Save and Back</span> 
                                        </a> 
                                    </div>
                                    <div class="icon-label"> 
                                        <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 8)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> 
                                            <span>For Committee</span> 
                                        </a> 
                                    </div>
                               <?php }elseif ($tmp_record['employee_appraisal_status'] == 8 &&  $this->user_access[$this->module_id]['project_hr'] == 1) {?>
                                    
                                    <div class="icon-label"> 
                                        <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 8)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
                                            <span>Save and Back</span> 
                                        </a> 
                                    </div>
                                    <div class="icon-label"> 
                                        <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 5)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> 
                                            <span>Approve</span> 
                                        </a> 
                                    </div>
                         <?php   

                        }


                    }
                    else{


                        if( ( $tmp_record['employee_appraisal_status'] == "" || $tmp_record['employee_appraisal_status'] == 1 || $tmp_record['employee_appraisal_status'] == 0 ) && ( $appraiser->user_id == $this->userinfo['user_id'] ) ){

                            ?>

                            <div class="icon-label"> 
                                <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 1)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
                                    <span>Save as Draft</span> 
                                </a> 
                            </div>
                            <div class="icon-label"> 
                                <a onclick="ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 3)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> 
                                    <span>Endorse</span> 
                                </a> 
                            </div>

                        <?php


                        }

                        if( $tmp_record['employee_appraisal_status'] == 3 && $commenter ){

                                $label = ( $last_commenter )? 'Conforme' : 'Endorse';


                            ?>

                            <div class="icon-label"> 
                                <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 3)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
                                    <span>Save and Back</span> 
                                </a> 
                            </div>
                            <div class="icon-label"> 
                                <a onclick="ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 3)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> 
                                    <span><?= $label ?></span> 
                                </a> 
                            </div>

                        <?php



                        }

                        if( $tmp_record['employee_appraisal_status'] == 4 && ( $appraisee['user_id'] == $this->userinfo['user_id'] ) ){


                            ?>

                            <div class="icon-label"> 
                                <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 4)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
                                    <span>Save and Back</span> 
                                </a> 
                            </div>
                            <div class="icon-label"> 
                                <a onclick="ajax_save('conformed', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 6)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> 
                                    <span>Save and Send</span> 
                                </a> 
                            </div>

                        <?php


                        }elseif ($tmp_record['employee_appraisal_status'] == 6 &&  $department_head['user_id'] == $this->userinfo['user_id']) {?>
                            
                            <div class="icon-label"> 
                                <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 6)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
                                    <span>Save and Back</span> 
                                </a> 
                            </div>
                            <div class="icon-label"> 
                                <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 7)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> 
                                    <span>Endorse</span> 
                                </a> 
                            </div>

                       <?php }elseif ($tmp_record['employee_appraisal_status'] == 7 &&  $division_head['user_id'] == $this->userinfo['user_id']) {?>
                            
                            <div class="icon-label"> 
                                <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 7)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
                                    <span>Save and Back</span> 
                                </a> 
                            </div>
                            <div class="icon-label"> 
                                <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 8)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> 
                                    <span>For Committee</span> 
                                </a> 
                            </div>
                       <?php }elseif ($tmp_record['employee_appraisal_status'] == 8 &&  $this->user_access[$this->module_id]['project_hr'] == 1) {?>
                            
                            <div class="icon-label"> 
                                <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 8)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
                                    <span>Save and Back</span> 
                                </a> 
                            </div>
                            <div class="icon-label"> 
                                <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>, '', 8)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> 
                                    <span>Approve</span> 
                                </a> 
                            </div>                            

                 <?php   }
                     }


                ?>

                <div class="icon-label"> <a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list"> <span>Back to list</span> </a> </div>

            </span> 

        </div>

    </div> 

<!-- for dynaminc fields -->
<table style="display:none;" class="strength_areas_improvement">
    <tr class="employee_strength">
        <td>
            <div style="float:left;">
               <!--  <input type="text" name="employee_strength[]" value="" /> -->
               <textarea name="employee_strength[]" style="width: 334px; height: 53px;"></textarea>
                                                                    
            </div>
            <div style="float:left;vertical-align:middle">
                <a original-title="" tooltip="Delete row" href="javascript:void(0)" class="icon-16-delete icon-button delete_strength_areas_improvement_row"></a>
            </div>
            <div class="clear"></div>
        </td>
    </tr>
    <tr class="areas_improvement">
        <td>
            <div style="float:left;">
               <!--  <input type="text" name="areas_improvement[]" value="" /> -->
                <textarea name="areas_improvement[]" style="width: 334px; height: 53px;"></textarea>
            </div>
            <div style="float:left;">
                <a original-title="" tooltip="Delete row" href="javascript:void(0)" class="icon-16-delete icon-button delete_strength_areas_improvement_row"></a>
            </div>
            <div class="clear"></div>
        </td>
    </tr>
    <tr class="coach_strength">
        <td>
            <div style="float:left;">
                <!-- <input type="text" name="coach_strength[]" value="" /> -->
                 <textarea name="coach_strength[]" style="width: 334px; height: 53px;"></textarea>
            </div>
            <div style="float:left;">
                <a original-title="" tooltip="Delete row" href="javascript:void(0)" class="icon-16-delete icon-button delete_strength_areas_improvement_row"></a>
            </div>
            <div class="clear"></div>
        </td>
    </tr>
    <tr class="coach_improvement">
        <td>
            <div style="float:left;">
                <!-- <input type="text" name="coach_improvement[]" value="" /> -->
                <textarea name="coach_improvement[]" style="width: 334px; height: 53px;"></textarea>
            </div>
            <div style="float:left;">
                <a original-title="" tooltip="Delete row" href="javascript:void(0)" class="icon-16-delete icon-button delete_strength_areas_improvement_row"></a>
            </div>
            <div class="clear"></div>
        </td>
    </tr>
</table>