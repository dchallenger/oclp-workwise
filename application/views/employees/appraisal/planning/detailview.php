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
table.valign tr td {
vertical-align:top;
}
table.valign-bottom tr td {
vertical-align:bottom;
}
table.valign form {
display:inline;
}

.textarea{
    width: 200px; 
    height: 60px;
/*    vertical-align:middle;*/

}

.input{
    width: 90px; 
}

DIV.scrollingdatagrid {
    overflow-x:auto;
    overflow-y:auto;
    position:relative;
    padding:0px;
}
DIV.scrollingdatagrid TABLE {
    width : 98.7%; /* Make room for scroll bar! */
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

    $level = $this->db->get_where('appraisal_competency_level', array('deleted' => 0));
    $core_rating = $this->db->get_where('appraisal_core_value_rating', array('deleted' => 0));


    $disable = 'disabled';
    $approved_disable = true;
    $approved_disable_other_field = 'disabled';
    $mid_disable = 'disabled';
    $weight_disable = 'disabled';
?>

<div>
    <h4>Performance Planning</h4>
    <form id="record-form" method="post">
        <input type="hidden" name="record_id" value="<?=$record_id?>" />
        <input type="hidden" name="employee_id" id="appraisee_id" value="<?=$appraisee['user_id']?>" />
        <input type="hidden" name="appraiser_id" value="<?=$appraiser->user_id?>" />
        <input type="hidden" name="div_head_id" value="<?= $division_head['user_id'] ?>"  />
        <input type="hidden" name="period_id" id="period_id" value="<?=$period->planning_period_id?>" />
        <input type="hidden" id="appraisal_year" value="<?=$period->year?>" />

        <table style="width: 100%;" border="0" class="default-table boxtype" id="main">
            <tbody>
                <tr>
                    <td style="background-color: #333333;" colspan="8">
                        <strong><span style="color: #ffffff;">JOB INFORMATION</span></strong>
                    </td>
                </tr>            
                <tr>
                    <td style="width:75%">
                        <table style="width: 100%;" border="0" class="default-table boxtype">
                            <tbody>
                                <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>EMPLOYEE NAME:</strong></td>
                                    <td style="width: 30%;">
                                        <input type="text" name="appraisee[fullname]" value="<?=$appraisee['firstname'] . ' ' . $appraisee['lastname']?>" size="40" disabled >
                                    </td>
                                    <td style="background-color: #c0c0c0; width: 20%;" ><strong>APPRAISAL PERIOD:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisal[appraisal_period]" value="<?=date('M d, Y', strtotime($period->date_from))?> - <?=date('M d, Y', strtotime($period->date_to))?> : <?=$period->planning_period?> " id="appraisal_period" size="40" maxlength="40" disabled >
                                    </td>
                                </tr>
                                <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>RANK:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisee[rank]" value="<?=$appraisee['job_rank']?>" size="40" disabled ></td>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>LAST APPRAISAL DATE:</strong></td>
                                    <td style="width: 30%;">
                                    <input type="text" disabled name="last_appraisal_date" value="<?=(date('Y-m-d', strtotime($appraisee['last_appraisal_date'])) != '1970-01-01') ? date('F d, Y', strtotime($appraisee['last_appraisal_date'])) : ' ' ?>" size="40" readonly="readonly"></td>
                                </tr>
                                <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>POSITION:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisee[position_name]" value="<?=$appraisee['position']?>" size="40" disabled ></td>
                                   
                                    
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>LAST PROMOTION DATE:</strong></td>
                                    <td style="width: 30%;"><input type="text" disabled value="<?=($appraisee['last_promotion_date'] != '0000-00-00' && $appraisee['last_promotion_date'] != ' ' ) ? date('F d, Y', strtotime($appraisee['last_promotion_date'])) : ' ' ?>" size="40"></td>
                                    
                                </tr>
                                <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>REPORTS TO:</strong></td>
                                    <td style="width: 30%;">
                                        <input type="text" name="appraiser[fullname]" value="<?=$appraiser->firstname . ' ' . $appraiser->lastname?>" size="40" disabled >
                                    </td>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>TENURE:</strong></td>
                                    <td style="width: 30%;">
                                        <input type="text" name="appraiser[tenure]" value="<?= $appraisee['tenure'] ?>" size="40" disabled >
                                    </td>
                                    
                                </tr>
                                <tr>
                                     <td style="background-color: #c0c0c0; width: 20%;"><strong>DEPARTMENT:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisee[department_name]" value="<?=$appraisee['department']?>" size="40" disabled ></td>
                                      <td style="background-color: #c0c0c0; width: 20%;"><strong>TOTAL WEIGHTED SCORE:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisee[total_weight_score]" value="" size="40" disabled ></td>
                                </tr>
                                <tr>
                                     <td style="background-color: #c0c0c0; width: 20%;"><strong>DEPARTMENT HEAD:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisee[department_name]" value="<?=$department_head['firstname'] . ' ' . $department_head['lastname']?>" size="40" disabled ></td>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>OVERALL RATING:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisee[overall_rating]" value="" size="40" disabled ></td>
                                </tr>
                                <tr>
                                      <td style="background-color: #c0c0c0; width: 20%;"><strong>DIVISION:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisee[department_head]" value="<?=$appraisee['division']?>" size="40" disabled ></td>
                                </tr>
                                <tr>
                                      <td style="background-color: #c0c0c0; width: 20%;"><strong>DIVISION HEAD:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisee[department_head]" value="<?=$division_head['firstname'] . ' ' . $division_head['lastname']?>" size="40" disabled ></td>
                                </tr>
                                <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>POSITION CLASSIFICATION:</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisee[position_class]" value="<?=$appraisee['position_class']?>" size="40" disabled ></td>
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
                                                <th style="width: 15%;">Total Weighted Score</th>
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
                    <td colspan="2" style="padding:0px">
                        <!--  <div class="scrollingdatagrid" style="width:1250px;max-height:700px;">  -->
                         <?php 
                            $ctr = 1; 
                            foreach ($form->result() as $criteria):
                                $column_count = count($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name']);
                            if (!$criteria->is_core): // if core
                            ?>
                            <input type="hidden" name="criteria[]" value="<?=$criteria->employee_appraisal_criteria_id?>" />

                            
                            <table border="1" class="default-table boxtype valign performance_objective" style="width:100%" ratio="<?= $criteria->ratio_weighter_score ?>">
                                
                                <thead>
                                    <tr>
                                        <td style="background-color: #333333;" colspan="<?= $column_count + 3?>">
                                            <strong><span style="color: #ffffff;">Section <?=$ctr?> - <?=$criteria->criteria_text?></span></strong>
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
                                            //header check is for balfour, disregard this. Temporary not deleted just in case
                                            if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name_header_check'])):
                                                if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id])): 
                                                    foreach ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name'] as $key => $column):
                                                        if ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name_header'][$key] != ""):                                        
                                            ?>
                                                            <td><b><?= $column ?></b><?= ($criteria_columns[$criteria->employee_appraisal_criteria_id]['field_required'][$key] == 1 ? '<span class="red font-large">*</span>' : '')?></td>             
                                            <?php
                                                        endif;                                          
                                                    endforeach;
                                                endif;                                      
                                            else:
                                                if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id])): 
                                                    foreach ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name'] as $key => $column):
                                                        if (!in_array($column, array('Actual','% Achieved','% Weight Average'))) {
                                            ?>
                                                            <td>
                                                                <b><?= $column ?></b>
                                                                <?= ($criteria_columns[$criteria->employee_appraisal_criteria_id]['field_required'][$key] == 1 ? '<span class="red font-large">*</span>' : '')?>
                                            <?php               if ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_tooltip'][$key] != "") { ?>
                                                                    <span style="display: inline-block; vertical-align: middle;">
                                                                        <a href="javascript:void(0)" tooltip="<?=$criteria_columns[$criteria->employee_appraisal_criteria_id]['column_tooltip'][$key]?>" class="icon-button icon-16-info description_tooltip" atitle="description" style="background-color:transparent;border: 1px solid transparent"></a>
                                                                        <!--  <label style="display:none"><?=$criteria_columns[$criteria->employee_appraisal_criteria_id]['column_tooltip'][$key]?></label> -->
                                                                    </span>
                                                    <?php       } ?>
                                                            </td>               
                                            <?php       }

                                                    endforeach;
                                                endif;                                      
                                            endif;
                                        ?>
                                        <!--
                                        <td align="center"><strong>Weight</strong>
                                        <span style="display: inline-block; vertical-align: middle;">
                                                <a href="javascript:void(0)" tooltip="This is agreed upon by the Coach and Team Member during the Performance Planning & Appraisal discussion, based on the impact and importance of the objective. " class="icon-button icon-16-info description_tooltip" atitle="description" style="background-color:transparent;border: 1px solid transparent"></a>
                                                <!-- <label style="display:none"><?=$criteria->tooltip?></label> 
                                            </span>
                                        </td>
                                        -->
                                    </tr>  
                                    <!-- column header of per section -->  




                                    <?php 

                                        $qctr = 1; 

                                        foreach($criteria_questions[$criteria->employee_appraisal_criteria_id]['questions'] as $key => $question):
                                    ?>
                            
                                     <tr id="question_trq<?=$key?>" class="perspective" perspectiveid="<?=$key?>">
                                        <td class="question_tdq<?=$key?>" width="300px" >
                                            <span style="display: inline-block; vertical-align: middle; margin-right: 8px;"><?=$qctr++?>.&nbsp;&nbsp;<?=$question?></span>

                                            <br />
                                             <?php
                                                if ($criteria_questions[$criteria->employee_appraisal_criteria_id]['tooltip'][$key] != ''):                            
                                            ?>
                                                    <span style="display: inline-block; vertical-align: middle;">
                                                        <a href="javascript:void(0)" tooltip="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['tooltip'][$key]?>" class="icon-button icon-16-info description_tooltip" atitle="description"></a>
                                                        <!-- <label style="display:none"><?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['tooltip'][$key]?></label> -->
                                                    </span>
                                            <?php 
                                                endif; 
                                                if ( ( $tmp_record['planning_status'] != 6 && $personal ) && !$approved_disable  ):
                                            ?>  
                                                    <span style="display: inline-block; vertical-align: middle;">
                                                        <a tooltip="Add row" href="javascript:void(0)" criteria="<?=$criteria->employee_appraisal_criteria_id?>" class="icon-16-add icon-button add_row" columnid="question<?=$key?>" question="q<?=$key?>"></a>
                                                    </span>
                                            <? endif; ?>

                                            <br />
                                            <br />

                                            <label>Key in Weight <span class="red font-large">*</span></label>
                                            <input type="text" class="key_weight" style="width:120px;" perspectiveid="<?=$key?>" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][key_weight]" value="<?= $tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key]['key_weight'] ?>" <?= $weight_disable ?> <?= $approved_disable_other_field ?> disabled />

                                        </td>
                                            
                                        <?php       

                                        if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id])): 
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

                                                        $column_array_val = $tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key];

                                                        if( isset($tmp_record['employee_appraisal_criteria_actual_question_array'][$criteria->employee_appraisal_criteria_id][$key]) ){

                                                            $actual_column_array_val = $tmp_record['employee_appraisal_criteria_actual_question_array'][$criteria->employee_appraisal_criteria_id][$key];

                                                        }

                                                        foreach ($column_name_array as $key1 => $column):
                                                            if (!in_array($column, array('Actual','% Achieved','% Weight Average'))) {
                                                                $column_class="";

                                                                if( $column == "% distribution" ){
                                                                    $column_class = "input distribution_".$key;
                                                                }

                                                                
                                                                    $approved_disable_field = 'disabled';
                                                                                                                                                                                 
                                                                ?>
                                                                <td class="questions_box" align="center">

                                                                <?php if( $column_code_array[$key1] == 'achieved' || $column_code_array[$key1] == 'weight_average' ): ?>
                                                                    <?php if ($column_type_array[$key1] == 1): ?>
                                                                        <textarea class="<?= $column_class ?> textarea <?= ($field_required_array[$key1] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$key][$column_name_id[$key1]]?>" name="actual[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][<?=$key1?>][]"  <?= $approved_disable_field ?> ><?= $actual_column_array_val[$key1][0]?></textarea>
                                                                   
                                                                    <?php elseif ($column_type_array[$key1] == 2): ?>
                                                                        <input type="text"  style="width:80px;" class="<?= $column_class ?> <?= ($field_required_array[$key1] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$key][$column_name_id[$key1]]?>" name="actual[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][<?=$key1?>][]" value="<?= $actual_column_array_val[$key1][0]?>"  <?= $approved_disable_field ?> >
                                                                    <?php endif;?>

                                                                <?php elseif( $column_code_array[$key1] == 'percent_distribution' ): ?>
                                                                    <?php if ($column_type_array[$key1] == 1): ?>
                                                                        <textarea class="<?= $column_class ?> textarea <?= ($field_required_array[$key1] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$key][$column_name_id[$key1]]?>" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][<?=$key1?>][]" <?= $weight_disable ?> ><?= $column_array_val[$key1][0]?></textarea>
                                                                   
                                                                    <?php elseif ($column_type_array[$key1] == 2): ?>
                                                                        <input type="text" style="width:80px;" class="<?= $column_class ?> <?= ($field_required_array[$key1] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$key][$column_name_id[$key1]]?>" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][<?=$key1?>][]" value="<?= $column_array_val[$key1][0]?>" <?= $weight_disable ?> >
                                                                    <?php endif;?>

                                                                <?php else: ?>
                                                                    <?php if ($column_type_array[$key1] == 1): ?>
                                                                        <textarea <?= ($personal ? '' : 'readonly="readonly"') ?> class="<?= $column_class ?> textarea <?= ($field_required_array[$key1] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$key][$column_name_id[$key1]]?>" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][<?=$key1?>][]" <?= $disable ?> <?= $approved_disable_field ?> ><?= $column_array_val[$key1][0]?></textarea>
                                                                   
                                                                    <?php elseif ($column_type_array[$key1] == 2): ?>
                                                                        <input type="text" style="width:80px;" class="<?= $column_class ?> <?= ($field_required_array[$key1] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$key][$column_name_id[$key1]]?>" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][<?=$key1?>][]" value="<?= $column_array_val[$key1][0]?>" <?= $approved_disable_field ?> >
                                                                    <?php endif;?>

                                                                <?php endif; ?>

                                                                </td>
                                                            <?php
                                                                }
                                                                        
                                                        endforeach;
                                                    endif;
                                                            ?>

                                                            
                                    </tr>
                                     
                                    <?php

                                                $per_question_count = count($tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key][0]);
                                                $per_question_count = ($per_question_count < 1 ? 1 : $per_question_count);
                                                $column_array_val = $tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key];


                                                if( isset($tmp_record['employee_appraisal_criteria_actual_question_array'][$criteria->employee_appraisal_criteria_id][$key]) ){

                                                    $actual_column_array_val = $tmp_record['employee_appraisal_criteria_actual_question_array'][$criteria->employee_appraisal_criteria_id][$key];

                                                }


                                                if ($per_question_count > 1){
                                                    for ($i=1; $i < $per_question_count; $i++){
                                            ?>
                                                        <tr class="additional">
                                                            <td align="center">
                                                                <?php if ( ( $personal && $tmp_record['planning_status'] < 6 ) && !$approved_disable ): ?>
                                                                    <span style="vertical-align: middle;" class="hidden del-button">
                                                                        <a class="icon-16-delete icon-button delete_row" href="javascript:void(0)" tooltip="Delete row" original-title=""></a>
                                                                    </span>
                                                                <?php endif; ?>

                                                            </td>
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

                                                                    foreach ($column_name_array as $key1 => $column):
                                                                        if (!in_array($column, array('Actual','% Achieved','% Weight Average'))) {
                                                                            $column_class="";

                                                                            if( $column == "% distribution" ){
                                                                                $column_class = "input distribution_".$key;
                                                                            }

                                                                            $approved_disable_field = 'disabled';
                                                                            ?>
                                                                                <td align="center">

                                                                                <?php if( $column_code_array[$key1] == 'actual' || $column_code_array[$key1] == 'actual_accomplished' ): ?>

                                                                                    <?php if ($column_type_array[$key1] == 1): ?>
                                                                                        <textarea class="<?= $column_class ?> textarea <?= ($field_required_array[$key1] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$key][$column_name_id[$key1]]?>" name="actual[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][<?=$key1?>][]" <?= $approved_disable_field ?> ><?= $actual_column_array_val[$key1][$i]?></textarea>
                                                                                   
                                                                                    <?php elseif ($column_type_array[$key1] == 2): ?>
                                                                                        <input type="text" class="<?= $column_class ?> <?= ($field_required_array[$key1] == 1 ? 'required ' : '') ?>" style="width:80px;" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$key][$column_name_id[$key1]]?>" name="actual[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][<?=$key1?>][]" value="<?= $actual_column_array_val[$key1][$i]?>" <?= $approved_disable_field ?> >
                                                                                    <?php endif;?>

                                                                                <?php elseif( $column_code_array[$key1] == 'percent_distribution' ): ?>

                                                                                    <?php if ($column_type_array[$key1] == 1): ?>
                                                                                        <textarea class="<?= $column_class ?> textarea <?= ($field_required_array[$key1] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$key][$column_name_id[$key1]]?>" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][<?=$key1?>][]" <?= $weight_disable ?> ><?= $column_array_val[$key1][$i]?></textarea>
                                                                                   
                                                                                    <?php elseif ($column_type_array[$key1] == 2): ?>
                                                                                        <input type="text" class="<?= $column_class ?> <?= ($field_required_array[$key1] == 1 ? 'required ' : '') ?>" style="width:80px;" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$key][$column_name_id[$key1]]?>" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][<?=$key1?>][]" value="<?= $column_array_val[$key1][$i]?>" <?= $weight_disable ?> >
                                                                                    <?php endif;?>


                                                                                <?php else: ?>

                                                                                    <?php if ($column_type_array[$key1] == 1): ?>
                                                                                        <textarea <?= ($personal ? '' : 'readonly="readonly"') ?> class="<?= $column_class ?> textarea <?= ($field_required_array[$key1] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$key][$column_name_id[$key1]]?>" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][<?=$key1?>][]" <?= $disable ?> <?= $approved_disable_field ?> ><?= $column_array_val[$key1][$i]?></textarea>
                                                                                   
                                                                                    <?php elseif ($column_type_array[$key1] == 2): ?>
                                                                                        <input type="text" class="<?= $column_class ?> <?= ($field_required_array[$key1] == 1 ? 'required ' : '') ?>" style="width:80px;" cname="<?=$column?>" placeholder="<?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['placeholder'][$key][$column_name_id[$key1]]?>" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][<?=$key1?>][]" value="<?= $column_array_val[$key1][$i]?>"  <?= $approved_disable_field ?> >
                                                                                    <?php endif;?>

                                                                                <?php endif; ?>

                                                                                </td>
                                                                            <?php
                                                                        }
                                                                    endforeach;
                                                                endif;
                                                            ?>
                                                        </tr>  
                                            <?php                                                
                                                    }
                                                }
                                            ?>
                                    <tr id="q<?=$key?>" hidden><td>&nbsp;</td></tr>
                                    
                                    <?php if( $criteria->with_comments == 1 ){ ?>
                                        <tr class="comment<?=$key?>">
                                            <td>
                                                <div class="icon-label">
                                                <a href="javascript:void(0)" class="icon-16-info" onclick="comment_box('<?=$criteria->employee_appraisal_criteria_id?>', '<?=$key?>')" ><span>View Comment</span></a>
                                                </div>
                                                <?php
                                                    $comment_count_qry = $this->db->query("SELECT COUNT(*) AS comment_count FROM {$this->db->dbprefix}appraisal_planning_comment  WHERE employee_appraisal_criteria_id = ".$criteria->employee_appraisal_criteria_id." AND employee_appraisal_criteria_question_id = ".$key." AND appraisee_id = ".$appraisee['user_id']." AND period_id = ".$period->planning_period_id);

                                                    if($comment_count_qry && $comment_count_qry->num_rows() > 0){
                                                        if ( $comment_count_qry->row()->comment_count > 0 ){
                                                            $comment_count = $comment_count_qry->row()->comment_count;
                                                    
                                                ?>
                                                    <span class="align-right bg-red" style="border-radius: 50px; color: #fff; font-size: 11px; line-height: normal; margin: 0 0 0 -10px; padding: 2px 5px; position: absolute; z-index: 1;">
                                                        <small><? echo $comment_count; ?></small>
                                                    </span>
                                                <?php
                                                        }
                                                    }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                    <?php if( $approvers && $approvers->num_rows() > 0 ):
                                            foreach( $approvers->result() as $approver ):?>

<!--                                     <tr>                                        
                                        <td colspan="<?= $column_count ?>">
                                            <table style="width:100%">
                                                <tr>
                                                    <td>
                                                        <div class="form-item odd ">
                                                            <?php
                                                                $mid_comment = "";
                                                                if( isset($tmp_record['employee_appraisal_criteria_mid_year_comments'][$criteria->employee_appraisal_criteria_id][$key]) ){
                                                                    $mid_comment = $tmp_record['employee_appraisal_criteria_mid_year_comments'][$criteria->employee_appraisal_criteria_id][$key]['mid_year_comments'][$approver->user_id];
                                                                }
                                                            ?>
                                                            <label>Mid-Year Performance Review Comments: (<?=$approver->firstname . ' ' . $approver->lastname?>)</label>
                                                            <div>    
                                                                <textarea style="width:100%;height:50px;" name="mid_year_comments[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][mid_year_comments][<?=$approver->user_id?>]" <?= $mid_disable ?> ><?= $mid_comment ?></textarea>
                                                            </div>        
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-item odd ">
                                                            <label>Year-end Performance Review Comments: (<?=$approver->firstname . ' ' . $approver->lastname?>)</label>
                                                            <div>    
                                                                <textarea style="width:100%;height:50px;" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][year_end_comments]" disabled><?= $tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key]['year_end_comments'] ?></textarea>
                                                            </div>        
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr> -->
                                    <?php endforeach;
                                        endif;?>  
                                    <?php endforeach; ?>




                                </tbody>
                            </table>
                        <?php 
                           /* else: //if core ?> 
                            <div class="clear"></div>
                             <br>
                            <table border="1" class="default-table boxtype valign" style="width:100%">
                            
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


                                        if( $competency_master_info->competency_master_code == 'attendance' ){

                                    ?>


                                    <tr>
                                        <td align=""><strong>Competencies / Values</strong></td>
                                        <td align=""><strong>Please Refer to Employees DTR Summary</strong></td>
                                        <td align=""><strong>Rating</strong></td>
                                        <td align="center">&nbsp;</td>
                                    </tr>

                                    <?php 

                                        }
                                        else{
                            
                                    ?>

                                    <tr>
                                        <td align=""><strong>Competencies / Values</strong></td>
                                        <?php if( $appraisee['rank_id'] >= 8){ ?><td align=""><strong>Self Comment</strong></td><?php } ?>
                                        <td align=""><strong>Coach Comment</strong></td>
                                        <td align="center">&nbsp;</td>
                                    </tr>

                                <?php

                                    }

                                    $core_ctr = 1;
                                 foreach ($core_values[$criteria->employee_appraisal_criteria_id] as $values) { 
                                    $competencies = $this->db->get_where('appraisal_competency', array('appraisal_competency_value_id' => $values->competency_value_id));
                                

                                    if( $competency_master_info->competency_master_code == 'attendance' ){

                                    ?>

                                    <tr id="core_value<?=$values->competency_value_id?>" class="competency_value">
                                        <td ><span style="display: inline-block; vertical-align: middle; margin-right: 8px;"><?=$core_ctr?>.  <?=$values->competency_value?><br/>
                                            <table style="width:270px;">
                                                <tbody><tr>
                                                            <td style="border-bottom:1px solid #dedede"><small><?=$values->competency_value_description?></small></td>
                                                        </tr>
                                                </tbody>
                                            </table> 
                                        </td>
                                        <td>&nbsp;</td>
                                        <td align="">
                                           <select name="competency[<?=$values->competency_value_id?>]['rating']" disabled>
                                                <option value="">Please Select...</option>
                                           </select>
                                        </td>
                                        
                                        <td>
                                            
                   
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <table style="width:100%">
                                                <tr>
                                                    <td>
                                                        <div class="form-item odd ">
                                                            <label>Mid-Year Performance Review Comments:</label>
                                                            <div>    
                                                                <?php if($tmp_record['employee_appraisal_competency_mid_comment_array']):
                                                                        $mid_comment = $tmp_record['employee_appraisal_competency_mid_comment_array'][$values->competency_value_id]['mid_year_comment'];
                                                                        else:
                                                                        $mid_comment = "";        
                                                                     endif;?>
                                                                <textarea name="attendance_mid_year[<?=$values->competency_value_id?>][mid_year_comment]" style="width:100%;" <?= $mid_disable ?>><?=$mid_comment?></textarea>
                                                            </div>        
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-item odd ">
                                                            <label>Year-end Performance Review Comments:</label>
                                                            <div>    
                                                                <?php if($tmp_record['employee_appraisal_competency_array']):
                                                                        $yr_comment = $tmp_record['employee_appraisal_competency_array'][$values->competency_value_id]['year_end_comment'];
                                                                        else:
                                                                        $yr_comment = "";        
                                                                     endif;?>
                                                                <textarea style="width:100%;" name="competency[<?=$values->competency_value_id?>][year_end_comment]" disabled><?=$yr_comment?></textarea>
                                                            </div>        
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>



                                    <?php
                                    }
                                    else{
                                    ?>



                                    <tr id="core_value<?=$values->competency_value_id?>" class="competency_value">
                                        <td ><span style="display: inline-block; vertical-align: middle; margin-right: 8px;"><?=$core_ctr?>.  <?=$values->competency_value?><br/>
                                            <table style="width:270px;">
                                                <tbody><tr>
                                                            <td style="border-bottom:1px solid #dedede"><small><?=$values->competency_value_description?></small></td>
                                                        </tr>
                                                </tbody>
                                            </table> 
                                            
                                        </td>   
                                        <?php if( $appraisee['rank_id'] >= 8){ ?>                                    
                                        <td>

                                            <?php

                                                $self_comment = "";

                                                if( isset($tmp_record['employee_appraisal_competency_array'][$values->competency_value_id]['self_comment'] )){

                                                    $self_comment = $tmp_record['employee_appraisal_competency_array'][$values->competency_value_id]['self_comment'];

                                                }

                                                $course_comment = "";

                                                if( isset($tmp_record['employee_appraisal_competency_array'][$values->competency_value_id]['course_comment'] )){

                                                    $course_comment = $tmp_record['employee_appraisal_competency_array'][$values->competency_value_id]['self_comment'];

                                                }

                                            ?>

                                            <textarea name="competency[<?=$values->competency_value_id?>][self_comment]" class="textarea"  disabled>
                                                <?= $self_comment ?>
                                            </textarea>
                                        </td>
                                        <?php } ?>
                                        <td align="">
                                           <textarea name="competency[<?=$values->competency_value_id?>][course_comment]" class="textarea"  disabled>
                                            <?= $course_comment ?>
                                           </textarea>
                                        </td>
                                        
                                        <td>
                                            
                   
                                        </td>
                                    </tr>

                                    <?php } ?>


                                 <tr id="<?=$values->competency_value_id?>">
                                    <td align="center" colspan="7">&nbsp;</td>
                                </tr>   


                                <?php $core_ctr++; } ?>

                                    </tbody>
                            </table>
                        <?php */ endif;
                            $ctr++;
                        endforeach;
                        ?>
                      <!--   <table border="1" class="default-table boxtype valign" style="width:100%">
                            <tr>
                                <td style="background-color: #333333;" colspan="8">
                                    <strong><span style="color: #ffffff;">Strengths and Areas For Improvement</span></strong>
                                </td>
                            </tr>
                        </table>
                        <table border="1" class="default-table boxtype valign" style="width:100%">
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
                                        <table class="employee_strength" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <td>
                                                        <div style="float:left;">
                                                            <p><strong>1. What are the employees strengths?</strong></p> 
                                                        </div>
                                                        <?php if( !$personal && ( $rater ) ){ ?>
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
                                                                    <div style="float:left;">
                                                                     
                                                                        <textarea name="employee_strength[]"  disabled style="width: 334px; height: 53px;" <?=($personal) ? 'readonly="readonly"' : '' ?>  <?= ( $tmp_record['planning_status'] == 3 ) ? 'readonly="readonly"' : '' ?>   ><?= $employee_strength_info ?></textarea>
                                                                    </div>
                                                                    <?php if( !$personal && ( $rater ) ){ ?>
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
                                        <table class="areas_improvement" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <td>
                                                        <div style="float:left;">
                                                            <p><strong>2. What areas of performance needs enhancement or improvement?</strong></p> 
                                                        </div>
                                                        <?php if( !$personal && ( $rater ) ){ ?>
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
                                                                    <div style="float:left;">
                                                                       
                                                                        <textarea style="width: 334px; height: 53px;" disabled name="areas_improvement[]" <?=($personal) ? 'readonly="readonly"' : '' ?> <?= ( $tmp_record['planning_status'] == 3 ) ? 'readonly="readonly"' : '' ?> ><?= $areas_improvement_info ?></textarea>
                                                                    </div>
                                                                    <?php if( !$personal && ( $rater ) ){ ?>
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
                                <tr>
                                    <td>
                                        <table class="coach_strength" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <td>
                                                        <div style="float:left;">
                                                            <p><strong>3. What are the coach's strengths?</strong></p> 
                                                        </div>
                                                        <?php if( $personal && !$approved_disable ){ ?>
                                                        <div style="float:left; margin:0px 2px;">
                                                            <span style="display: inline-block; vertical-align: middle;">
                                                                <a tooltip="Add row" href="javascript:void(0)" class="icon-16-add icon-button add_strength_areas_improvement_row" rel="coach_strength"></a>
                                                            </span>
                                                        </div>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            </thead>
                                            <tbody class="coach_strength">
                                                <?php 
                                                    foreach( $tmp_record['employee_appraisal_coach_strength'] as $coach_strength_info ){
                                                        ?>
                                                            <tr class="coach_strength">
                                                                <td>
                                                                    <div style="float:left;">
                                                                       
                                                                        <textarea style="width: 334px; height: 53px;" name="coach_strength[]" <?= $disable ?> <?= $approved_disable_other_field ?> <?=(!$personal) ? 'readonly="readonly"' : '' ?> <?= ( $tmp_record['planning_status'] == 3 ) ? 'readonly="readonly"' : '' ?>  ><?= $coach_strength_info ?></textarea>
                                                                    </div>
                                                                    <?php if( $personal && !$approved_disable ){ ?>
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
                                        <table class="coach_improvement" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <td>
                                                        <div style="float:left;">
                                                            <p><strong>4. What areas of coach's performance needs enhancement or improvement?</strong></p> 
                                                        </div>
                                                        <?php if( $personal && !$approved_disable ){ ?>
                                                        <div style="float:left; margin:0px 2px;">
                                                            <span style="display: inline-block; vertical-align: middle;">
                                                                <a tooltip="Add row" href="javascript:void(0)" class="icon-16-add icon-button add_strength_areas_improvement_row" rel="coach_improvement"></a>
                                                            </span>
                                                        </div>
                                                        <?php } ?>
                                                        <div class="clear"></div>
                                                    </td>
                                                </tr>
                                            </thead>
                                            <tbody class="coach_improvement">
                                                <?php 
                                                    foreach( $tmp_record['employee_appraisal_coach_improvement'] as $coach_improvement_info ){
                                                        ?>
                                                            <tr class="coach_improvement">
                                                                <td>
                                                                    <div style="float:left;">
                                                                       
                                                                       <textarea name="coach_improvement[]" <?= $disable ?> <?= $approved_disable_other_field ?> <?=(!$personal) ? 'readonly="readonly"' : '' ?> <?= ( $tmp_record['planning_status'] == 3 ) ? 'readonly="readonly"' : '' ?> style="width: 334px; height: 53px;"><?= $coach_improvement_info ?></textarea>

                                                                    </div>
                                                                    <?php if( $personal && !$approved_disable ){ ?>
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
                     <!--    </table>
 -->
                        <table border="1" class="default-table boxtype valign" style="width:100%">
                            <tr>
                                <td style="background-color: #333333;" colspan="8">
                                    <strong><span style="color: #ffffff;">OVERALL RATING</span></strong>
                                </td>
                            </tr>
                        </table>
                        <table border="1" class="default-table boxtype valign">
                            <thead>
                                <tr>
                                    <td><strong>GENERAL CRITERIA</strong></td>
                                    <td><strong>KEY IN WEIGHT</strong></td>
                                    <td><strong>SELF RATING</strong></td>
                                    <td><strong>COACH'S RATING</strong></td>
                                    <td><strong>SECTION RATING</strong></td>
                                    <td><strong>WEIGH IN (%)</strong></td>
                                    <td><strong>TOTAL WEIGHTED SCORE</strong></td>
                                </tr>
                            </thead>
                            <tbody>

                                <?php

                                    foreach ($form->result() as $criteria):
                                        $column_count = count($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name']);
                                        if (!$criteria->is_core): // if core

                                        ?>

                                        <tr>
                                            <td><strong><?= $criteria->criteria_text ?></strong></td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td><?= $criteria->ratio_weighter_score ?></td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    
                                        <?php

                                        foreach($criteria_questions[$criteria->employee_appraisal_criteria_id]['questions'] as $key => $question):


                                            $weight = ( $tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key]['key_weight'] != 0 )? $tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key]['key_weight'] : 0 ; 

                                        ?>

                                            <tr>
                                                <td><?= $question ?></td>
                                                <td class="perspective_weight_<?= $key ?>"><?= $weight.' %' ?></td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                            </tr>

                                        <?php
                                        endforeach;
                                      /*  else:

                                        ?>

                                        <tr>
                                            <td><strong><?= $criteria->criteria_text ?></strong></td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td><?= $criteria->ratio_weighter_score ?></td>
                                            <td>&nbsp;</td>
                                        </tr>


                                        <?php 

                                        foreach ($core_values[$criteria->employee_appraisal_criteria_id] as $values) { 

                                        ?>

                                        <tr>
                                            <td><?= $values->competency_value ?></td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>

                                        <?php

                                        }
                                        */


                                        endif;

                                    endforeach;

                                ?>

                            </tbody>


                        </table>


                      <!--    </div> -->
                    </td>
                </tr>
            </tbody>
        </table>
<br>
<br>

    </form>


    <div class="clear"></div>
    <div class="form-submit-btn">
        <div class="icon-label-group">

                <span>

                   <div class="icon-label"> 
                        <a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list"> 
                            <span>Back to list</span> 
                        </a> 
                    </div>

                </span>                    
        </div>    
    </div>
</div>    
</div>    
