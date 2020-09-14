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
vertical-align:middle;
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
    width : 99.6%; /* Make room for scroll bar! */
    margin:0px;
    border:0px;
    border-collapse:separate;
    table-layout: fixed;
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
    $cnt_approver = count($approvers);
    $approver_flag = false;     
    foreach ($approvers as $key => $approver) {
        if ($approver['approver'] == $this->userinfo['user_id'])
        {
            $approver_flag = true;       
            // $cnt_approver = 1;
        }
    }
  $post_acc = $this->user_access[$this->module_id]['post'];
?>
<!-- <div style="overflow-x: auto; width:1300px;padding-right:10px"> -->
<div class="wizard-leftcol">
  <ul>
        <li style="width:50%" >
            <a class="leftcol-control" rel="fg-1" href="javascript:void(0)">
                <span class="wizard-ctr">1</span><br />
                <span class="wizard-label" style="width:90%">Appraisal Form</span>
            </a>
        </li>
        <li style="width:50%">
            <a class="leftcol-control" rel="fg-2" href="javascript:void(0)">
                <span class="wizard-ctr">2</span><br />
                <span class="wizard-label" style="width:90%">For Immediate Superior</span>
            </a>
        </li>

    <!--     <li style="width:50%"> <a href="javascript:void(0)" class="leftcol-control"><span class="wizard-ctr">1</span><br>
      <span style="width:90%" class="wizard-label">Appraisal Form</span></a> </li>
        <li style="width:20%"> <a href="javascript:void(0)"  class="leftcol-control"><span class="wizard-ctr">2</span><br>
      <span style="width:90%" class="wizard-label">For Immediate Superior</span></a> </li> -->
  </ul>
</div>

<div>
    <div fg_id="1" id="fg-1" class="wizard-type-form hidden current-wizard wizard-first">
    <h4>Performance Planning and Appraisal</h4>
    <form id="record-form" method="post">
        <input type="hidden" name="record_id" value="<?=$record_id?>" />
        <input type="hidden" name="employee_id" value="<?=$appraisee['user_id']?>" />
        <input type="hidden" name="appraiser_id" value="<?=$appraiser->user_id?>" />
        <input type="hidden" name="period_id" value="<?=$period->employee_appraisal_period_id?>" />

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
                                        <input type="text" name="appraisee[fullname]" value="<?=$appraisee['firstname'] . ' ' . $appraisee['lastname']?>" size="30" readonly="readonly">
                                    </td>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>REPORTS TO:</strong></td>
                                    <td style="width: 30%;">
                                        <input type="text" name="appraiser[fullname]" value="<?=$appraiser->firstname . ' ' . $appraiser->lastname?>" size="30" readonly="readonly">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>POSITION</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisee[position_name]" value="<?=$appraisee['position']?>" size="30" readonly="readonly"></td>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>RATER'S DIRECT SUPERIOR</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraiser_direct_superior[position_name]" value="<?=$appraiser_direct_superior['firstname'] . ' ' . $appraiser_direct_superior['lastname']?>" size="30" readonly="readonly"></td>
                                </tr>
                                <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>DEPARTMENT/DIVISION</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisee[department_name]" value="<?=$appraisee['department']?>" size="30" readonly="readonly"></td>
                                    <td style="background-color: #c0c0c0; width: 20%;" rowspan="2"><strong>OVERALL RATING</strong></td>
                                    <td style="width: 30%;" rowspan="2"><input type="text" name="appraisee[overall_rating]" value="<?=$tmp_record['employee_appraisal_or_total_score']?>" size="30" readonly="readonly"></td>
                                </tr>
                                <tr>
                                    <td style="background-color: #c0c0c0; width: 20%;"><strong>APPRAISAL PERIOD</strong></td>
                                    <td style="width: 30%;"><input type="text" name="appraisal[appraisal_period]" value="<?=$period->appraisal_year?> : <?=$period->appraisal_period?>" id="appraisal_period" size="30" maxlength="30" readonly="readonly"></td>
                                </tr>
                            </tbody>
                        </table>                    
                    </td>
<!--                     <td style="width:25%;padding:0px">
                        <table>
                            <tbody>
                                <tr>
                                    <td>RECOMMENDED ACTION</td>
                                </tr>
                                <tr>
                                    <td>
                                        <ul class="rating_scale">
                                            <li><?= form_checkbox('ra[1]',1,(isset($tmp_record['employee_appraisal_recommended_action_array'][1])) ? TRUE : FALSE)?> Retain job/rank</li>
                                            <li><?= form_checkbox('ra[2]',1,(isset($tmp_record['employee_appraisal_recommended_action_array'][2])) ? TRUE : FALSE)?> Promote (Attach justification with critical incidents)</li>
                                            <li><?= form_checkbox('ra[3]',1,(isset($tmp_record['employee_appraisal_recommended_action_array'][3])) ? TRUE : FALSE)?> Transfer</li>
                                            <li><?= form_checkbox('ra[4]',1,(isset($tmp_record['employee_appraisal_recommended_action_array'][4])) ? TRUE : FALSE)?> Upgrade Job (subject to job evaluation)</li>
                                            <li><?= form_checkbox('ra[5]',1,(isset($tmp_record['employee_appraisal_recommended_action_array'][5])) ? TRUE : FALSE)?> Downgrade Job (subject to job evaluation)</li>
                                            <li><?= form_checkbox('ra[6]',1,(isset($tmp_record['employee_appraisal_recommended_action_array'][6])) ? TRUE : FALSE)?> Terminate</li>
                                        </ul>
                                    </td>
                                </tr>                            
                            </tbody>
                        </table>
                    </td> -->
                </tr>
                <tr>
                    <td colspan="2" style="padding:0px">
                        <table style="width: 100%;" border="0" class="default-table boxtype">
                            <tbody>
                                <tr>
                                    <td style="background-color: #333333;" colspan="8">
                                        <strong><span style="color: #ffffff;">Rating Scale</span></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <?php $ctr = 1; foreach ($form->result() as $criteria): ?>
                                            <div><strong>For Section <?=$ctr?> - <?=$criteria->criteria_text?>: <?=$criteria->ratio_weighter_score?>% of Overall Rating</strong></div>
                                            <?php
                                                $ctr1 = 0;
                                                foreach($rating_scale[$criteria->employee_appraisal_criteria_id]['scale'] as $rating):
                                                    if ($rating_scale[$criteria->employee_appraisal_criteria_id]['title'][$ctr1] != ''):
                                            ?>
                                                    <div><b><?= $rating_scale[$criteria->employee_appraisal_criteria_id]['title'][$ctr1] ?></b></div>
                                            <?php endif; ?>
                                                    <div><?= $rating_scale[$criteria->employee_appraisal_criteria_id]['scale'][$ctr1] ?></div>
                                        <?php 
                                                    $ctr1++;                    
                                                endforeach;
                                                print '<br />';                     
                                            $ctr++; 
                                            endforeach;                                             
                                        ?>
                                    </td>
                                </tr> 
                            </tbody>
                        </table> 
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding:0px">
                        <div class="scrollingdatagrid" style="width:1100px;max-height:700px;">
                            <?php 
                            $ctr = 1; 
                            $ctr_head = 1;
                            foreach ($form->result() as $criteria):
                                $column_count = count($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name']) + 2;

                            ?>

                            <input type="hidden" name="criteria[]" value="<?=$criteria->employee_appraisal_criteria_id?>" />
                            <!-- <table style="width: 1500px;" border="0" class="default-table boxtype valign"> -->
                            <table border="0" class="default-table boxtype valign">
                                <thead>
                                    <tr>
                                        <td style="background-color: #333333;" colspan="<?= $column_count + 6 + ($cnt_approver*2) ?>">
                                            <strong><span style="color: #ffffff;">Section <?=$ctr_head?> - <?=$criteria->criteria_text?></span></strong>
                                            <span style="float:right;"><a href="javascript:void(0)" class="show_hide"><span style="color: #ffffff;">Hide</span></a></span>
                                        </td>
                                    </tr>
                                </thead>                            
                                <tbody>   

                                    <?php
                                    $ctr_head++;
                                        if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name_header_check'])):
                                            print '<tr>';
                                            $arr_val = array_count_values($criteria_columns['column_name_header']);
                                            $array_val_added = array();
                                            foreach ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name_header'] as $key => $value):
                                                if ($value != '' && !in_array($value, $array_val_added)):
                                                    $count = $arr_val[$value];
                                                    array_push($array_val_added, $value);
                                                    print '<td>&nbsp;</td><td colspan="'.$count.'" align="center"><b>'.$value.'</b></td>';

                                                else:
                                                    if ($value == ''):
                                                        print '<td>&nbsp;</td><td rowspan="2"><b>'.$criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name'][$key].'</b></td>';
                                                    endif;
                                                endif;
                                            endforeach; 
                                            print '</tr>';      
                                        endif;
                                    ?>
                                    <!-- column header of per section -->
                                    <tr>
                                        <?php 
                                            $ctr = 1;                                        
    										if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name_header_check'])):
    											if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id])): 
    												foreach ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name'] as $key => $column):
    													if ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name_header'][$key] != ""):										
    										?>
                                                            <?php if ($ctr == 1) print '<td>&nbsp;</td>'; ?>
    														<td><b><?= $column ?></b><?= ($criteria_columns[$criteria->employee_appraisal_criteria_id]['field_required'][$key] == 1 ? '<span class="red font-large">*</span>' : '')?></td>             
    										<?php
                                                        $ctr++;
                                                        endif;  										
    												endforeach;
    											endif;										
    										else:
    											if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id])): 
                                                
                                                if ($approver_flag) {
                                                    $cnt_approvers = 1;
                                                }else{
                                                    $cnt_approvers = $cnt_approver;
                                                }

                                                for ($i=0; $i < $cnt_approvers; $i++): 
                                                     $cols = array();
                                                    
    												foreach ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name'] as $key => $column):
    										?>
                                                        <?php if ($ctr == 1) print '<td colspan="4">&nbsp;</td>'; ?>
    													<td><b><?= $column ?></b><?= ($criteria_columns[$criteria->employee_appraisal_criteria_id]['field_required'][$key] == 1 ? '<span class="red font-large">*</span>' : '')?></td>               
    										<?php
                                                        $ctr++;
                                                        $cols[] = $column;
    												endforeach;

                                                    print '<td><b>'.implode(' x<br>', $cols).'</b></td>';
                                                    endfor;
                                                     // print '<td><b>Total</b></td>';
    											endif;										
    										endif;
                                        ?>
                                    </tr>  
                                    <?php 
                                        foreach ($question_header[$criteria->employee_appraisal_criteria_id]['header'] as $header_id => $header) {
                                            $percent = $question_header[$criteria->employee_appraisal_criteria_id]['percentage'][$header_id];

                                            ?>
                                        <tr>
                                            <td colspan="4" ><b><?=$header?> <?= ($percent != 0) ? '('.$percent.')': '' ;?> </b></td>
                                        </tr> 
                                       <?php $cnt_q = 1;
                                        foreach ($criteria_questions[$header_id]['questions'] as $key => $question) { ?>
                                        <tr>
                                            <td colspan="4">
                                                 <span style="display: inline-block; vertical-align: middle; margin-right: 8px;"><?=$cnt_q?>.&nbsp;&nbsp;<?=$question?></span>
                                                <?php
                                                    if ($criteria_questions[$header_id]['tooltip'][$key] != ''):                            
                                                ?>
                                                        <span style="display: inline-block; vertical-align: middle;">
                                                            <a href="javascript:void(0)" tooltip="View description" class="icon-button icon-16-info description_tooltip" atitle="description"></a>
                                                            <label style="display:none"><?=$criteria_questions[$header_id]['tooltip'][$key]?></label>
                                                        </span>
                                                <?php 
                                                    endif; 
                                                ?> 
                                            </td>
                                            <?php 
                                                    if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id])): 
                                                        $column_type_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['column_type'];
                                                        $column_name_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name'];
                                                        $appraisal_period_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['payroll_period'];
                                                        $field_required_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['field_required'];
                                                        $column_class_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['class'];

/*                                                        array_shift($column_type_array);
                                                        array_shift($column_name_array);
                                                        array_shift($appraisal_period_array);
                                                        array_shift($field_required_array);
                                                        array_shift($column_class_array);*/

                                                    $column_array_val = $tmp_record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key];
                                                      for ($q=0; $q < $cnt_approver; $q++):     
                                                      $approver = $approvers[$q]['approver'];
                                                        foreach ($column_name_array as $key1 => $column):
                                                            $show = false;
                                                            if ($appraisal_period_array[$key1] == $period->appraisal_period):
                                                                $show = true;                                                
                                                            endif;                                                    
                                                            if ($column_type_array[$key1] == 1):                                                             
                                                ?>
                                                                <td><textarea <?= ($show ? '' : 'readonly="readonly"') ?> class="<?= ($field_required_array[$key1] == 1 ? 'required ' : '') ?>" cname="<?=$column?>" style="width:100%" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][<?=$key1?>][]"><?= $column_array_val[$key1][0]?></textarea></td>
                                                <?php
                                                            elseif ($column_type_array[$key1] == 2):    
                                                            
                                                            $display = '';
                                                            if(!$post_acc)
                                                                { 
                                                                    $display = "style='display:none'"; 
                                                                    if ($approver == $this->userinfo['user_id']) 
                                                                        { 
                                                                            $display = ""; 
                                                                        }
                                                                }


                                                ?>
                                                                <td <?=$display?>><input <?= ($show ? $personal ? 'readonly="readonly"' : '' : 'readonly="readonly"') ?> class="<?= ($field_required_array[$key1] == 1 ? 'required ' : '') ?><?= $column_class_array[$key1]?>" cname="<?=$column?>" type="text" criteria="<?=$criteria->employee_appraisal_criteria_id?>" size="5" class="<?=strtolower(str_replace(' ', '', $column))?>" value="<?= $column_array_val[$key1][$approver][0]?>" name="cq[<?=$criteria->employee_appraisal_criteria_id?>][<?=$key?>][<?=$key1?>][<?=$approver?>][]" 
                                                                /></td>
                                                <?php
                                                            endif;
                                                        endforeach;?>
                                                        <td <?=$display?>><input  type="text" size="5" /></td>
                                                  <?php      endfor; 
                                                         // print '<td><input  type="text" size="5"/></td>';
                                                    endif;
                                                ?>
                                        </tr> 

                                      <?php  $cnt_q++;
                                        } ?>
                                        
                                    <?php } ?>
                                    
                                </tbody>
                            </table>
                            <?php $ctr++; endforeach;?>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <br />           
        <br />

        <table style="width: 100%;" border="0" class="default-table boxtype valign">
            <tbody>
                <tr>
                    <td style="background-color: #333333;" colspan="6">
                        <strong><span style="color: #ffffff;">OVERALL RATING</span></strong>
                    </td>
                </tr>    
                <tr>
                    <td colspan="2"><strong>GENERAL CRITERIA</strong></td>
                    <td><strong>OVERALL WEIGHT</strong></td>
                    <td><strong>SECTION RATINGS</strong></td>
                    <td><strong>OVERALL RATING</strong></td>
                    <td><strong>EQUIVALENT PERCENTAGE</strong></td>
                </tr>                                                         
                <?php
                    $total_percentage = 0;
                    foreach ($form->result() as $criteria):  
                        $total_percentage +=  $criteria->ratio_weighter_score;         
                ?>
                        <tr>
                            <input type="hidden" id="inp_sec_rating<?=$criteria->employee_appraisal_criteria_id?>" name="section_rating[<?=$criteria->employee_appraisal_criteria_id?>]" value="<?=$tmp_record['employee_appraisal_criteria_question_sec_rating_array'][$criteria->employee_appraisal_criteria_id]?>">
                            <input type="hidden" id="inp_over_rating<?=$criteria->employee_appraisal_criteria_id?>" name="overall_rating[<?=$criteria->employee_appraisal_criteria_id?>]" value="<?=$tmp_record['employee_appraisal_criteria_question_overal_rating_array'][$criteria->employee_appraisal_criteria_id]?>">
                            <td colspan="2"><?=$criteria->criteria_text?>%</td>
                            <td><?=$criteria->ratio_weighter_score?>%</td>
                            <td id="sec_rating<?=$criteria->employee_appraisal_criteria_id?>" weight="<?=$criteria->ratio_weighter_score?>"><?=$tmp_record['employee_appraisal_criteria_question_sec_rating_array'][$criteria->employee_appraisal_criteria_id]?></td>
                            <td id="over_rating<?=$criteria->employee_appraisal_criteria_id?>" class="overall_rating"><?=$tmp_record['employee_appraisal_criteria_question_overal_rating_array'][$criteria->employee_appraisal_criteria_id]?></td>
                            <td>&nbsp;</td>
                        </tr>                
                <?php                    
                    endforeach;
                ?>  
                <tr>
                    <input type="hidden" id="inp_total_score" name="total_score" value="<?=$tmp_record['employee_appraisal_or_total_score']?>">
                    <td colspan="2" align="right">Total Weight must equal 100%</td>
                    <td><?=$total_percentage?>%</td>
                    <td align="right"><b>Total Score</b></td>
                    <td id="total_score"><?=$tmp_record['employee_appraisal_or_total_score']?></td>
                    <td><b>Total Percentage</b> </td>
                </tr> 
                <tr>
                    <td align="center" colspan="5">TEAM MEMBER / RATEE'S COMMENTS<span class="red font-large">*</span></td>
                    <td></td>
                </tr> 
                <tr>
                    <td align="center" colspan="5"><textarea <?= (!$personal ? 'readonly="readonly"' : '') ?> class="required" cname="TEAM MEMBER / RATEE'S COMMENTS" style="width:100%" name="employee_appraisal_or_ratees_comments"><?=$tmp_record['employee_appraisal_or_ratees_comments']?></textarea></td>
                    <td align="left">
                        <p>GENERAL COMMENTS<span class="red font-large">*</span></p>
                        <p><?= form_radio('employee_appraisal_or_gen_comments',1,($tmp_record['employee_appraisal_or_ratees_comments'] = 1 ? TRUE : FALSE),(!$personal ? "disabled='disabled'" : ""))?> I agree with the rating</p>
                        <p><?= form_radio('employee_appraisal_or_gen_comments',0,($tmp_record['employee_appraisal_or_ratees_comments'] = 0 ? TRUE : FALSE),(!$personal ? "disabled='disabled'" : ""))?> I disagree with the rating</p>
                    </td>
                </tr>   

            </tbody>
        </table> 
        <br /> 
    <div class="page-navigator align-right">
        <div class="btn-prev-disabled"> <a href="javascript:void(0)"><span>Prev</span></a></div>
        <div class="btn-prev hidden"> <a onclick="prev_wizard()" href="javascript:void(0)" class="tipsy-autons"><span>Prev</span></a></div>
        <div class="btn-next"> <a onclick="next_wizard()" href="javascript:void(0)" class="tipsy-autons"><span>Next</span></a></div>
        <div class="btn-next-disabled hidden"> <a href="javascript:void(0)"><span>Next</span></a></div>
    </div>

   </div>
<br/>
 <div fg_id="2" id="fg-2" class="wizard-type-form hidden">
  
  <table style="width: 100%;" border="0" class="default-table boxtype valign">
                <tr>
                    <td align="center" colspan="4">COACH / RATER'S COMMENTS (to be accomplished only after the PA Discussion<span class="red font-large">*</span></td>
                </tr>                                                                               
                <tr>
                    <td align="center" colspan="4"><textarea <?= ($personal ? 'readonly="readonly"' : '') ?> class="required" cname="COACH / RATER'S COMMENTS" style="width:100%" name="employee_appraisal_or_raters_comments"><?=$tmp_record['employee_appraisal_or_raters_comments']?></textarea></td>
                </tr> 
                <tr>
                    <td align="center" colspan="4">DIVISION / DEPARTMENT HEAD COMMENTS<span class="red font-large">*</span></td>
                </tr>                  
                <tr>
                    <td align="center" colspan="4"><textarea <?= ($personal ? 'readonly="readonly"' : '') ?> class="required" cname="DIVISION / DEPARTMENT HEAD COMMENTS" style="width:100%" name="employee_appraisal_or_div_dep_comments"><?=$tmp_record['employee_appraisal_or_raters_comments']?></textarea></td>
                </tr> 
                <tr>
                    <td align="center" colspan="4">HUMAN RESOURCES MANAGEMENTS COMMENTS<span class="red font-large">*</span></td>
                </tr>                  
                <tr>
                    <td align="center" colspan="4"><textarea <?= ($personal ? 'readonly="readonly"' : '') ?> class="required" cname="HUMAN RESOURCES MANAGEMENTS COMMENTS" style="width:100%" name="employee_appraisal_or_hr_comments"><?=$tmp_record['employee_appraisal_or_raters_comments']?></textarea></td>
                </tr>  
                 </table>
<!--         <table style="width: 100%;" border="0" class="default-table boxtype valign">
            <tbody>
                <tr>
                    <td style="background-color: #333333;" colspan="5">
                        <strong><span style="color: #ffffff;">Performance Development Planning</span></strong>
                    </td>
                </tr>    
                <tr>
                    <td colspan="5">The performance development plan discussion provides an opportunity to identify the employee's development needs. In areas where improvement or growth can be made, the team member and coach need to make specific plans and commitments.</td>
                </tr>      
                <tr>
                    <td colspan="2">
                        Competencies
                        <?php if ($tmp_record['status'] < 4): ?>
                            <span style="display: inline-block; vertical-align: middle;"><a tooltip="Add row" href="javascript:void(0)" class="icon-16-add icon-button add_row_comp1"></a></span>
                        <?php endif ?>
                    </td>
                    <td>Describe Strengths<span class="red font-large">*</span></td>
                    <td>Development plan to maximize strengths<span class="red font-large">*</span><br /> [Learning from: Self, Others, Experience/ <br /> On-the Job Tranining (OJT) and formal Training]</td>
                    <td>Resources/Support Needed<span class="red font-large">*</span></td>
                </tr>
                <tr style="display:none" class="tmp_html">
                    <td align="center"width="35px">
                        <?php if ($tmp_record['status'] < 4): ?>
                            <span style="vertical-align: middle;" class="del-button">
                                <a class="icon-16-delete icon-button delete_row" href="javascript:void(0)" tooltip="Delete row" original-title=""></a>
                            </span>                            
                        <?php endif; ?>
                    </td>                
                    <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Competencies" style="width: 100%;" name="pdp_comp1[]"></textarea></td>
                    <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Strengths" style="width: 100%;" name="pdp_ds1[]"></textarea></td>
                    <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Maximize Strengths" style="width: 100%;" name="pdp_dp1[]"></textarea></td>
                    <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Support Needed" style="width: 100%;" name="pdp_resources1[]"></textarea></td>
                </tr>             
                <?php
                    $counta = count($tmp_record['employee_appraisal_pdp_comp1_array']);
                    for ($i=0; $i < $counta; $i++) { 
                ?>
                        <tr class="additional">
                            <td align="center" width="35px">
                                <?php if ($tmp_record['status'] < 4): ?>
                                    <span style="vertical-align: middle;" class="hidden del-button">
                                        <a class="icon-16-delete icon-button delete_row" href="javascript:void(0)" tooltip="Delete row" original-title=""></a>
                                    </span>                  
                                <?php endif; ?>          
                            </td>
                            <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Competencies" style="width: 100%;" name="pdp_comp1[]"><?=$tmp_record['employee_appraisal_pdp_comp1_array'][$i]?></textarea></td>
                            <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Strengths" style="width: 100%;" name="pdp_ds1[]"><?=$tmp_record['employee_appraisal_pdp_ds1_array'][$i]?></textarea></td>
                            <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Maximize Strengths" style="width: 100%;" name="pdp_dp1[]"><?=$tmp_record['employee_appraisal_pdp_dp1_array'][$i]?></textarea></td>
                            <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Support Needed" style="width: 100%;" name="pdp_resources1[]"><?=$tmp_record['employee_appraisal_pdp_resources1_array'][$i]?></textarea></td>
                        </tr> 
                <?php
                    }
                ?>                                                                                                                                    
                <tr id="competencies1">
                    <td colspan="4">&nbsp;</td>
                </tr>              
                <tr>             
                    <td colspan="2">
                        Competencies
                        <?php if ($tmp_record['status'] < 4): ?>
                            <span style="display: inline-block; vertical-align: middle;"><a tooltip="Add row" href="javascript:void(0)" class="icon-16-add icon-button add_row_comp2"></a></span>
                        <?php endif; ?>
                    </td>
                    <td>Describe Areas for Improvement<span class="red font-large">*</span></td>
                    <td>Development plan to maximize strengths<span class="red font-large">*</span> <br /> [Learning from: Self, Others, Experience/ <br /> On-the Job Tranining (OJT) and formal Training]</td>
                    <td>Resources/Support Needed<span class="red font-large">*</span></td>
                </tr>                                                                                                                                    
                <tr style="display:none" class="tmp_html">
                    <td align="center" width="35px">
                        <?php if ($tmp_record['status'] < 4): ?>
                            <span style="vertical-align: middle;" class="hidden del-button">
                                <a class="icon-16-delete icon-button delete_row" href="javascript:void(0)" tooltip="Delete row" original-title=""></a>
                            </span>       
                        <?php endif; ?>                     
                    </td>                 
                    <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Competencies" style="width: 100%;" name="pdp_comp2[]"></textarea></td>
                    <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Strengths" style="width: 100%;" name="pdp_ds2[]"></textarea></td>
                    <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Maximize Strengths" style="width: 100%;" name="pdp_dp2[]"></textarea></td>
                    <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Support Needed" style="width: 100%;" name="pdp_resources2[]"></textarea></td>
                </tr>
                <?php
                    $counta = count($tmp_record['employee_appraisal_pdp_comp2_array']);
                    for ($i=0; $i < $counta; $i++) { 
                ?>            
                        <tr class="additional">
                            <td align="center" width="35px">
                                <?php if ($tmp_record['status'] < 4): ?>
                                    <span style="vertical-align: middle;" class="hidden del-button">
                                        <a class="icon-16-delete icon-button delete_row" href="javascript:void(0)" tooltip="Delete row" original-title=""></a>
                                    </span>                  
                                <?php endif; ?>                                
                            </td>                        
                            <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Competencies" style="width: 100%;" name="pdp_comp2[]"><?=$tmp_record['employee_appraisal_pdp_comp2_array'][$i]?></textarea></td>
                            <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Strengths" style="width: 100%;" name="pdp_ds2[]"><?=$tmp_record['employee_appraisal_pdp_ds2_array'][$i]?></textarea></td>
                            <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Maximize Strengths" style="width: 100%;" name="pdp_dp2[]"><?=$tmp_record['employee_appraisal_pdp_dp2_array'][$i]?></textarea></td>
                            <td><textarea <?=($max_payroll_period == $period->appraisal_period ? '' : 'readonly="readonly"')?> class="required" cname="Support Needed" style="width: 100%;" name="pdp_resources2[]"><?=$tmp_record['employee_appraisal_pdp_resources2_array'][$i]?></textarea></td>
                        </tr>  
                <?php
                    }
                ?>                                 
                <tr id="competencies2">
                    <td colspan="4">&nbsp;</td>
                </tr>                                     
            </tbody>
        </table>
        <p>Your signatures indicate that the above-mentioned development plans are mutually agreed upon by both coach and team member, and will form part of the team member's performance development plan for the following year.</p> 
        <table style="width: 100%;" border="0" class="default-table boxtype">
            <tbody>   
                <tr>
                    <td>
                        <p>TEAM MEMBER / RATEE'S SIGNATURE OVER PRINTED NAME</p>
                        <input readonly="readonly" type="text" style="width:100%" value="<?=$appraisee['firstname'] . ' ' . $appraisee['lastname']?>">
                    </td>
                    <td>
                        <p>COACH / RATER'S SIGNATURE OVER PRINTED NAME</p>
                        <input readonly="readonly" type="text" style="width:100%" value="<?=$appraiser->firstname . ' ' . $appraiser->lastname?>">
                    </td>
                </tr>                                                         
                <tr>
                    <td>
                        <p>DATE<span class="red font-large">*</span></p>
                        <input <?= (!$personal ? 'disabled="disabled"' : '') ?> cname="PDP RATEE'S SIGN DATE" type="text" style="width:96%" class="input-text date required" name="employee_appraisal_pdp_rates_sign_date" value="<?=($tmp_record['employee_appraisal_pdp_rates_sign_date'] != '' ? date('m/d/Y',strtotime($tmp_record['employee_appraisal_pdp_rates_sign_date'])) : '')?>">
                    </td>
                    <td>
                        <p>DATE<span class="red font-large">*</span></p>
                        <input <?= ($personal ? 'disabled="disabled"' : '') ?> cname="PDP RATER'S SIGN DATE" type="text" style="width:95%" class="input-text date required" name="employee_appraisal_pdp_raters_sign_date" value="<?=($tmp_record['employee_appraisal_pdp_raters_sign_date'] != '' ? date('m/d/Y',strtotime($tmp_record['employee_appraisal_pdp_raters_sign_date'])) : '')?>">
                    </td>
                </tr>                         
            </tbody>
        </table> -->
    <!--     <?php 
    	if ($personal):
    	?>
            <br />
            <table style="width: 100%;" border="0" class="default-table boxtype">
                <tbody> 
                    <tr>
                        <td style="background-color: #333333;" colspan="2">
                            <strong><span style="color: #ffffff;">Ratee's Acceptance</span></strong>
                        </td>
                    </tr>
                    <?php if ($apraiser_comments && $apraiser_comments->num_rows() > 0) { ?>
    					<tr>
                        	<td colspan="2">
                            	<p style=" font-weight: bold">Rater's Comments</p>
                            </td>
                        </tr> 					
    					<?php foreach ($apraiser_comments->result() as $row){ ?>                      
                            <tr>                  
                                <td>					
                                    <p><?=$row->appraiser_comments?></p>
                                </td>
                                <td align="right">
                                    <p><?=date('d-M-y',strtotime($row->date_created))?></p>
                                </td>                            
                            </tr>
                         <?php 
    					}
                    }                 
    				if ($apraisee_comments && $apraisee_comments->num_rows() > 0){ 
    				?>
    					<tr>
                        	<td colspan="2">
                            	<p style=" font-weight: bold">Previous Comments</p>
                            </td>
                        </tr>                   
    				<?php foreach ($apraisee_comments->result() as $row){ ?>
                        <tr>                  
                            <td>                
                                <p><?=$row->appraisee_comments?></p>
                            <td align="right">
                                <p><?=date('d-M-y',strtotime($row->date_created))?></p>
                            </td>                            
                        </tr>                                
                    <?php
    					}
    				}
    				?>
                    <tr>
                        <td colspan="2">
                            <p><?= form_checkbox('ratees_acceptance',1,false)?> Accept</p>
                        </td>
                    </tr>                                                         
                    <tr>
                        <td colspan="2">
                            <p>Comments</p>
                            <textarea name="ratees_comments" cols="" rows="10" style="width:100%"></textarea>
                        </td>
                    </tr>                         
                </tbody>
            </table>
        <?php else: ?>
            <table style="width: 100%;" border="0" class="default-table boxtype">
                <tbody> 
                    <tr>
                        <td style="background-color: #333333;" colspan="5">
                            <strong><span style="color: #ffffff;">Rater's Approval</span></strong>
                        </td>
                    </tr>
                    <?php if ($apraisee_comments && $apraisee_comments->num_rows() > 0) { ?>
    					<tr>
                        	<td>
                            	<p style=" font-weight: bold">Rater's Comments</p>
                            </td>
                        </tr> 					
    					<?php foreach ($apraisee_comments->result() as $row){ ?>                      
                            <tr>                  
                                <td>					
                                    <p><?=$row->appraisee_comments?></p>
                                </td>
                                <td>
                                    <p><?=date('d-M-y',strtotime($row->date_created))?></p>
                                </td>                            
                            </tr>
                         <?php 
    					}
                    }                  
                    if ($apraiser_comments && $apraiser_comments->num_rows() > 0){ ?>
    					<tr>
                        	<td colspan="2">
                            	<p style=" font-weight: bold">Previous Comments</p>
                            </td>
                        </tr>                                
                        <?php foreach ($apraiser_comments->result() as $row){	?>                      
                        	<tr>                  
     	                       	<td>					
    								<p><?=$row->appraiser_comments?></p>
                            	</td>
                            	<td align="right">
                                	<p><?=date('d-M-y',strtotime($row->date_created))?></p>
                                </td>                            
                        	</tr>
                         <?php 
    					 } 
    				}
    			    ?>   
                    <tr>
                        <td colspan="2">
                            <p>
    							<?= form_radio('raters_approval',1,false)?> Approved
                                <?= form_radio('raters_approval',0,false)?> Disapproved
                            </p>
                        </td>
                    </tr>                                                             
                    <tr>
                        <td colspan="2">
                            <p>Comments</p>
                            <textarea name="raters_comments" cols="" rows="10" style="width:100%"></textarea>
                        </td>
                    </tr>                         
                </tbody>
            </table>    
        <?php endif; ?>  -->   
       <table style="width: 100%;" border="0" class="default-table boxtype">
        <tbody>
            <tr>
                <td colspan="3 ">RECOMMENDED ACTION</td>
            </tr>
            <tr>
                <td><?= form_checkbox('ra[1]',1,(isset($tmp_record['employee_appraisal_recommended_action_array'][1])) ? TRUE : FALSE)?> Retain job/rank</td>
                <td><?= form_checkbox('ra[2]',1,(isset($tmp_record['employee_appraisal_recommended_action_array'][2])) ? TRUE : FALSE)?> Promote (Attach justification with critical incidents)</td>
                <td><?= form_checkbox('ra[3]',1,(isset($tmp_record['employee_appraisal_recommended_action_array'][3])) ? TRUE : FALSE)?> Transfer</td>
            </tr>
            <tr>
                <td><?= form_checkbox('ra[4]',1,(isset($tmp_record['employee_appraisal_recommended_action_array'][4])) ? TRUE : FALSE)?> Upgrade Job (subject to job evaluation)</td>
                <td><?= form_checkbox('ra[5]',1,(isset($tmp_record['employee_appraisal_recommended_action_array'][5])) ? TRUE : FALSE)?> Downgrade Job (subject to job evaluation)</td>
                <td><?= form_checkbox('ra[6]',1,(isset($tmp_record['employee_appraisal_recommended_action_array'][6])) ? TRUE : FALSE)?> Terminate</td>
                    
               
            </tr>                            
        </tbody>
    </table>   
        <br />  
        <table style="width: 100%;" border="0" class="default-table boxtype">
            <tbody>   
                <tr>
                    <td>
                        <p>TEAM MEMBER / RATEE'S SIGNATURE OVER PRINTED NAME</p>
                        <input readonly="readonly" type="text" style="width:100%" value="<?=$appraisee['firstname'] . ' ' . $appraisee['lastname']?>">
                    </td>
                    <td>
                        <p>COACH / RATER'S SIGNATURE OVER PRINTED NAME</p>
                        <input readonly="readonly" type="text" style="width:100%" value="<?=$appraiser->firstname . ' ' . $appraiser->lastname?>">
                    </td>
                </tr>                                                         
                <tr>
                    <td>
                        <p>DATE<span class="red font-large">*</span></p>
                        <input <?= (!$personal ? 'disabled="disabled"' : '') ?> type="text" cname="RATING RATEE'S SIGN DATE" style="width:96%" class="input-text date required" name="employee_appraisal_or_rates_sign_date" value="<?=($tmp_record['employee_appraisal_or_rates_sign_date'] != '' ? date('m/d/Y',strtotime($tmp_record['employee_appraisal_or_rates_sign_date'])) : '')?>">
                    </td>
                    <td>
                        <p>DATE<span class="red font-large">*</span></p>
                        <input <?= ($personal ? 'disabled="disabled"' : '') ?> type="text" cname="RATING RATER'S SIGN DATE" style="width:95%" class="input-text date required" name="employee_appraisal_or_raters_sign_date" value="<?=($tmp_record['employee_appraisal_or_raters_sign_date'] != '' ? date('m/d/Y',strtotime($tmp_record['employee_appraisal_or_raters_sign_date'])) : '')?>">
                    </td>
                </tr>                         
            </tbody>
        </table>
    </form>
 
    <div class="clear"></div>

        


     <div class="form-submit-btn">
        <div class="icon-label-group">
            <?php if ($tmp_record['status'] < 2): ?>
                <span>
                    <div class="icon-label"> <a onclick="ajax_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> <span>Save as Draft</span> </a> </div>
                    <div class="icon-label"> <a onclick="ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> <span>For &amp; Discussion</span> </a> </div>
                </span>
            <?php endif; ?>

            <?php 
                if ($tmp_record['status'] == 2):
                    if ($appraisee['user_id'] == $this->userinfo['user_id']):
            ?>
                        <span>
                            <div class="icon-label">
                                <a href="javascript:void(0);" class="icon-16-disk icon-conforme" rel="record-save"> 
                                    <span>Conforme</span>
                                </a> 
                            </div>
                        </span>
                    <?php else: ?>
                        <span>
                            <div class="icon-label"> <a onclick="ajax_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> <span>Save as Draft</span> </a> </div>
                            <div class="icon-label"> <a onclick="ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> <span>For &amp; Discussion</span> </a> </div>
                        </span>                    
            <?php
                    endif; 
                endif;
            ?>

            <?php 
                if ($tmp_record['status'] == 3):
                    if ($appraisee['user_id'] != $this->userinfo['user_id']):
            ?>
                        <span>
                            <div class="icon-label"> 
                                <a onclick="ajax_save('email_approved', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> 
                                    <span>Save and Approved</span> 
                                </a> 
                            </div>
                        </span>

            <?php 
                    endif;
                endif;
            ?>   

            <?php if ($tmp_record['status'] == 4): ?>         
                <script type="text/javascript">
                    $(document).ready(function () {                
                        $('#record-form input, #record-form textarea').attr('disabled', 'disabled');
                    });
                </script>  
            <?php endif; ?> 

            <?php if ($tmp_record['status'] == 3 && $appraisee['user_id'] == $this->userinfo['user_id']): ?>         
                <script type="text/javascript">
                    $(document).ready(function () {                
                        $('#record-form input, #record-form textarea').attr('disabled', 'disabled');
                    });
                </script>  
            <?php endif; ?>
            <?php if ($appraisee['user_id'] == $this->userinfo['user_id']): ?>         
                <script type="text/javascript">
                    $(document).ready(function () {                
                        $('.rating_scale input').attr('disabled', 'disabled');
                    });
                </script>  
            <?php endif; ?>                     
            <div class="icon-label"> <a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list"> <span>Back to list</span> </a> </div>
            <!-- <div class="icon-label add-more-div"><a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add-more"><span>Add</span></a></div>  -->  
        </div>  
    </div>
    </div>
</div>    