<script type="text/javascript"><?=$template->js?></script>
<style type="text/css"><?=$template->css?></style>

<h4>Performance Appraisal Form</h4>
<form id="record-form" method="post">
    <input type="hidden" name="record_id" value="<?=$record_id?>" />
    <input type="hidden" name="employee_id" value="<?=$appraisee['user_id']?>" />
    <input type="hidden" name="appraiser_id" value="<?=$appraiser->user_id?>" />
    <input type="hidden" name="period_id" value="<?=$period->employee_appraisal_period_id?>" />

    <table style="width: 100%;" border="0" class="default-table boxtype">
    	<tbody>
    		<tr>
    			<td style="background-color: #333333;" colspan="4">
    				<strong><span style="color: #ffffff;">JOB INFORMATION</span></strong>
    			</td>
    		</tr>
    		<tr>
    			<td style="background-color: #c0c0c0; width: 20%;"><strong>Appraisee</strong></td>
    			<td style="width: 30%;">
                    <input type="text" name="appraisee[fullname]" value="<?=$appraisee['firstname'] . ' ' . $appraisee['lastname']?>" size="45" readonly="readonly">
                </td>
    			<td style="background-color: #c0c0c0; width: 20%;"><strong>Appraiser</strong></td>
    			<td style="width: 30%;">
                    <input type="text" name="appraiser[fullname]" value="<?=$appraiser->firstname . ' ' . $appraiser->lastname?>" size="45" readonly="readonly">
                </td>
    		</tr>
    		<tr>
    			<td style="background-color: #c0c0c0; width: 20%;">&nbsp;<strong>Job Title</strong></td>
    			<td style="width: 30%;"><input type="text" name="appraisee[position_name]" value="<?=$appraisee['position']?>" size="45" readonly="readonly"></td>
    			<td style="background-color: #c0c0c0; width: 20%;"><strong>Job Title</strong></td>
    			<td style="width: 30%;"><input type="text" name="appraiser[position_name]" value="<?=$appraiser->position?>" size="45" readonly="readonly"></td>
    		</tr>
    		<tr>
    			<td style="background-color: #c0c0c0; width: 20%;"><strong>Date Joined</strong></td>
    			<td style="width: 30%;"><input type="text" name="appraisee[employed_date]" value="<?=display_date($this->config->item('display_date_format'), strtotime($appraisee['employed_date']))?>" size="45" readonly="readonly"></td>
    			<td style="background-color: #c0c0c0; width: 20%;"><strong>Appraisal Period</strong></td>
    			<td style="width: 30%;"><input type="text" name="appraisal[appraisal_period]" value="<?=$period->appraisal_year?> : <?=$period->appraisal_period?>" id="appraisal_period" size="45" maxlength="45" readonly="readonly"></td>
    		</tr>
    		<tr>
    			<td style="background-color: #c0c0c0; width: 20%;"><strong>Department</strong></td>
    			<td style="width: 30%;"><input type="text" name="appraisee[department_name]" value="<?=$appraisee['department']?>" size="45" readonly="readonly"></td>
    			<td style="background-color: #c0c0c0; width: 20%;"><strong>Appraisal Date</strong></td>
    			<td style="width: 30%;"><input type="text" name="appraisal[appraisal_date]" value="<?=isset($period->appraisal_date) ? display_date($this->config->item('display_date_format'), strtotime($period->appraisal_date)) : display_date($this->config->item('display_date_format'), strtotime(date('Y-m-d H:i:s')))?>" id="appraisal_date" size="45" maxlength="45" readonly="readonly"></td>
    		</tr>
    	</tbody>
    </table>

    <?php $ctr = 1; $total_mws=0;  $final_total1 = 0; $final_total2 = 0;
    if (!$record){
        $tmp_record = $record_previous;
    }
    else{
        $tmp_record = $record;   
    }    
    foreach ($form->result() as $criteria):
        ?>

    <input type="hidden" name="criteria[]" value="<?=$criteria->employee_appraisal_criteria_id?>" />
    <table style="width: 100%;" border="0" class="default-table boxtype">
        <tbody>
            <tr>
                <td style="background-color: #333333;" colspan="<?=$period_count + 3?>">
                    <strong><span style="color: #ffffff;">Part <?=$ctr?> : <?=$criteria->criteria_text?> (<?=$criteria->ratio_weighter_score?>%)</span></strong>
                </td>
            </tr>
            <tr>
                <td align="center" colspan="5">
                    <b>
                        *Standard:
                    <?php 
                        $num = 0;
                        foreach ($criteria_questions_options->result() as $scale):?>
                        <?=$scale->appraisal_scale?> - <?=$scale->appraisal_scale_times?>
                    <?php 
                        if ($num < $criteria_questions_options->num_rows() - 1) {
                            echo ';';
                        }
                        $num++;
                        endforeach;?>
                    </b>
                </td>
            </tr>
            <tr>
                <td><b>Targets</b></td>
                <td><b><center>Scale</center></b></td>
                <td colspan="2"><b><center>Weight</center></b></td>
                <td><b><center>Annual</center></b></td>
            </tr>        
            <tr>
                <td></td>
                <td>
                    <table width="100%">
                        <tr>
                            <td><b><?=$labels[0]?></b></td>
                            <td align="right"><b><?=$labels[count($labels) - 1]?></b></td>
                        </tr>
                    </table>                
                </td>
                <?php 
                    if ($period_count > 0) :
                        for ($i=1; $i <= $period_count; $i++){
                ?>
                            <td align="center"><b>S<?=$i?></b></td>
                <?php
                        }
                    endif;
                ?>
                <td align="center"><b>Average</b></td>
            </tr>
            <?php

            ?>
            <?php 
                $qctr = 1; 
                foreach($criteria_questions[$criteria->employee_appraisal_criteria_id]['questions'] as $key => $question):
                    if ($criteria_questions[$criteria->employee_appraisal_criteria_id]['headers'][$key] != ''):
            ?>     
                        <tr>
                            <td colspan="5" align="left"><b><?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['headers'][$key]?></b></td>                            
                        </tr>
                <?php 
                    endif; 

                    if (array_key_exists($key, $record['employee_appraisal_criteria_question_array'])):
                        $q_exists = false;
                    else:
                        $q_exists = true;
                    endif;

                    if (!$record){
                        $q_exists = false;
                    }

                    if ($criteria_questions[$criteria->employee_appraisal_criteria_id]['deleted'][$key] && !array_key_exists($key, $record['employee_appraisal_criteria_question_array'])):
                        $q_exists = true;
                    endif;
                ?>
            <tr>
                <td width="30%">
                    <?=$qctr++?>.&nbsp;&nbsp;<?=$question?>
                    <?php
                        if ($criteria_questions[$criteria->employee_appraisal_criteria_id]['tooltip'][$key] != ''):                            
                    ?>
                            <br />
                            <span style="padding-left:18px">
                                <a href="javascript:void(0)" tooltip="View description" class="icon-button icon-16-info description_tooltip" atitle="description"></a>
                                <label style="display:none"><?=$criteria_questions[$criteria->employee_appraisal_criteria_id]['tooltip'][$key]?></label>
                            </span>
                    <?php endif; ?>                    
                </td>
                <td>
                    <table width="100%">
                        <tr>
                        <?php 
                        $start = 1;
                        foreach($criteria_questions_options->result() as $option):
                        ?>
                        <td align="center">
                            <?=
                            form_radio(
                                'q[' . $key . ']', 
                                $option->appraisal_scale_times,($option->appraisal_scale_times == $record['employee_appraisal_criteria_question_array'][$key] ? true : false),($personal ? "disabled='disabled'" : $closed ? "disabled='disabled'" : $q_exists ? "disabled='disabled'" : "")
                                )                                
                            ?><br />
                            <?=$option->appraisal_scale_times?>                            
                        </td>
                        <?php endforeach;?>
                        </tr>
                    </table>
                </td>

                <?php 
                    if ($period_count > 0) :
                        for ($i=1; $i <= $period_count; $i++){
                ?>
                            <td align="center" width="5%" class="cq_col[<?=$key?>]">
                                <input readonly type="text" value="<?=$tmp_record['employee_appraisal_criteria_question_weight'][$key][$i]?>" size="5" name="cq_weight[<?=$key?>][<?=$i?>]" class="cq_weight<?=$key?> col<?=$i?>_<?=$criteria->employee_appraisal_criteria_id?>"/>
                            </td>
                <?php
                        }
                    endif;
                ?>            
                <td align="center" width="10%"><input readonly value="<?=$tmp_record['employee_appraisal_criteria_question_average'][$key]?>" type="text" size="5" class="cq_ave<?=$criteria->employee_appraisal_criteria_id?>" name="cq_ave[<?=$key?>]"/></td>
            </tr>

            <script type="text/javascript">
                $('input[name="q[<?=$key?>]"]')
                .change(function() {
                    $('input[name="cq_weight[<?=$key?>][<?=$period->appraisal_period?>]"]')
                        .val($(this).val() * <?=$multiplier?>);

                    $('input[name="cq_ave[<?=$key?>]"]"')
                        .val(get_average("cq_weight<?=$key?>"));

                    $('input[name="total_weight_score[<?=$criteria->employee_appraisal_criteria_id?>][<?=$period->appraisal_period?>]"]')
                        .val(get_total_score("col<?=$period->appraisal_period?>_<?=$criteria->employee_appraisal_criteria_id?>"));                        

                    $('input[name="cq_ave_total[<?=$criteria->employee_appraisal_criteria_id?>]"]')
                        .val(get_total_ave("cq_ave<?=$criteria->employee_appraisal_criteria_id?>"));                          

                    $('input[name="final_pa_score[<?=$period->appraisal_period?>]"]')
                        .val(get_final_total_score("total_score<?=$period->appraisal_period?>"));    

                    $('input[name="final_pa_score_average"]')
                        .val(get_final_total_ave("total_score_average"));                                                                      
                });

                $('input[name="q[<?=$key?>]"]:checked').trigger('change');
            </script>

            <?php 
                endforeach;
            ?>
            <tr>
                <td style="background-color: #c0c0c0;"><strong>Maximum Weighted Score - <?=$criteria->max_weighted_score?></strong></td>
                <td style="text-align: center; background-color: #c0c0c0;"><strong> Part <?=$ctr++?> : Total Score:</strong></td>
                <?php 
                    if ($period_count > 0) :
                        for ($i=1; $i <= $period_count; $i++){
                ?>
                            <td width="5%">
                                <input type="text" size="5" class="total_score<?=$i?>" value="<?=$tmp_record['employee_appraisal_criteria_total_weighted_score'][$criteria->employee_appraisal_criteria_id][$i]?>" readonly name="total_weight_score[<?=$criteria->employee_appraisal_criteria_id?>][<?=$i?>]"/>
                            </td>
                <?php
                        }
                    endif;
                ?>  
                <td align="center" width="10%"><input readonly class="total_score_average" value="<?=$tmp_record['employee_appraisal_criteria_question_average_total'][$criteria->employee_appraisal_criteria_id]?>" type="text" size="5" class="" name="cq_ave_total[<?=$criteria->employee_appraisal_criteria_id?>]" /></td>
            </tr>
        </tbody>
    </table>
    <?php 
    $total_mws    += $criteria->mws;
    $final_total1 += $s1_total;
    $final_total2 += $s2_total;
    endforeach;?>
    
    <table style="width: 100%;" border="0" class="default-table boxtype">
        <tr>
            <td width="20%"><b>Total Maximum Weighted Score - <?=$total_mws?></b></td>
            <td align="center"><b>FINAL PERFORMANCE APPRAISAL SCORE:</b></td>
            <?php 
                if ($period_count > 0) :
                    for ($i=1; $i <= $period_count; $i++){
            ?>
                        <td width="5%"><input type="text" size="5" value="<?=$tmp_record['employee_appraisal_final_pa_score'][$i]?>" readonly name="final_pa_score[<?=$i?>]"/></td>
            <?php
                    }
                endif;
            ?>             
            <td align="center" width="10%"><input type="text" size="5" value="<?=$tmp_record['employee_appraisal_final_pa_score_average']?>" readonly name="final_pa_score_average"/></td>
        </tr>
    </table>
    <table style="width: 100%;" border="0" class="default-table boxtype">
        <tr>
            <td width="20%" style="background-color: #c0c0c0;" align="center" colspan="3"><b>OVERALL PERFORMANCE</b></td>
        </tr>
        <tr>
            <td align="center"><b>Final Score</b></td>
            <td align="center"><b>Description</b></td>
            <td align="center"><b>Point Grade</b></td>
        </tr>
        <tr><td align="center">83.34 - 100.00</td><td align="center">Exemplary</td><td align="center">3</td></tr>        
        <tr><td align="center">66.68 - 83.33</td><td align="center">Full Successful</td><td align="center">2.5</td></tr>        
        <tr><td align="center">50.01 - 66.67</td><td align="center">Successful</td><td align="center">2</td></tr>        
        <tr><td align="center">33.34 - 50.00</td><td align="center">Needs Improvement</td><td align="center">1.5</td></tr>        
        <tr><td align="center">0 - 33.33</td><td align="center">Unacceptable</td><td align="center">1</td></tr>        
    </table>    
    <?php
    $display_prev = FALSE;
    if ($period->appraisal_period >= 2) {
        $display_prev = TRUE;
    }
    ?>
    <table style="width: 100%;" border="0" class="default-table boxtype">
        <tbody>
            <tr>
                <td style="background-color: #333333;" colspan="4">
                    <strong><span style="color: #ffffff;">Part <?=$ctr++?> : Performance Summary</span></strong>
                </td>
            </tr>        
            <tr>
                <td width="2%" colspan="1">A.</td>
                <td colspan="3" valign="bottom">
                    Key Strengths
                    <?php if ($display_prev):?>
                    <a href="javascript:void(0)" tooltip="View previous entry" class="icon-16-info pa_summary" atitle="Key Strengths" style="background-position: 50% 200%;display: inline-block;height: 17px;width: 30px;"></a>
                    <div class="hidden prev-appraisal"><?=$record_previous['key_strengths']?></div>
                    <?php endif;?>
                </td>
            </tr>
            <tr>
                <td colspan="1">&nbsp;</td>
                <td colspan="3">
                    <textarea name="key_strengths" style="width: 98%" <?=($personal ? "disabled='disabled'" : $closed ? "disabled='disabled'" : "")?>><?=$record['key_strengths']?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="1">B.</td>
                <td colspan="3">
                    Areas for Improvement
                    <?php if ($display_prev):?>
                    <a href="javascript:void(0)" tooltip="View previous entry" class="icon-16-info pa_summary" atitle="Areas for Improvement" style="background-position: 50% 200%;display: inline-block;height: 17px;width: 30px;"></a>
                    <div class="hidden prev-appraisal"><?=$record_previous['areas_for_improvement']?></div>
                    <?php endif;?>
                </td>
            </tr>
            <tr>
                <td colspan="1">&nbsp;</td>
                <td colspan="3">
                    <textarea name="areas_for_improvement" style="width: 98%" <?=($personal ? "disabled='disabled'" : $closed ? "disabled='disabled'" : "")?>><?=$record['areas_for_improvement']?></textarea>
                </td>
            </tr>
            <tr>
                <td width="2%">C.</td>
                <td>Individual Development Plan
                    <?php if ($display_prev):?>
                    <a href="javascript:void(0)" tooltip="View previous entry" class="icon-16-info idp" atitle="Individual Development Plan" style="background-position: 50% 200%;display: inline-block;height: 17px;width: 30px;"></a>
                    <div class="hidden prev-appraisal">
                        <div style="float:left;width:50%">
                            <?php
                                foreach ($record_previous['individual_development_plan'] as $key => $value) {
                            ?>
                                    <p><?php echo $value ?></p>
                            <?php
                                }
                            ?>
                        </div>
                        <div style="float:left;width:50%">
                            <?php
                                foreach ($record_previous['target_completion_date'] as $key => $value) {
                            ?>
                                    <p><?php echo $value ?></p>
                            <?php
                                }
                            ?>                            
                        </div>
                        <br clear="left" />
                    </div>
                    <?php endif;?>                    
                </td>
                <td width="2%">D.</td>
                <td>Target Completion Date</td>
            </tr>
            <?php
                for ($i=0; $i < 5; $i++) {
            ?>
                    <tr>
                        <td width="2%">&nbsp;</td>
                        <td><textarea name="individual_dp[]" style="width: 98%" <?=($personal ? "disabled='disabled'" : $closed ? "disabled='disabled'" : "")?>><?=$record['individual_development_plan'][$i]?></textarea></td>
                        <td width="2%">&nbsp;</td>
                        <td><textarea name="target_cd[]" style="width: 96%" <?=($personal ? "disabled='disabled'" : $closed ? "disabled='disabled'" : "")?>><?=$record['target_completion_date'][$i]?></textarea></td>
                    </tr>            
            <?
                }
            ?>
            <tr>
                <td colspan="1">E.</td>
                <td colspan="4">
                    Appraiser's Summary
                    <?php if ($display_prev):?>
                    <a href="javascript:void(0)" tooltip="View previous entry" class="icon-16-info pa_summary" atitle="Appraiser's Summary" style="background-position: 50% 200%;display: inline-block;height: 17px;width: 30px;"></a>
                    <div class="hidden prev-appraisal"><?=$record_previous['appraiser_summary']?></div>
                    <?php endif;?>               
                </td>
            </tr>
            <tr>
                <td colspan="1">&nbsp;</td>
                <td colspan="4">
                    <textarea name="appraiser_summary" style="width: 98%" <?=($personal ? "disabled='disabled'" : $closed ? "disabled='disabled'" : "")?>><?=$record['appraiser_summary']?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="1">F.</td>
                <td colspan="4">
                    Appraisee's Summary
                    <?php if ($display_prev):?>
                    <a href="javascript:void(0)" tooltip="View previous entry" class="icon-16-info pa_summary" atitle="Appraisee's Summary" style="background-position: 50% 200%;display: inline-block;height: 17px;width: 30px;"></a>
                    <div class="hidden prev-appraisal"><?=$record_previous['appraisee_summary']?></div>
                    <?php endif;?>
                </td>
            </tr>
            <tr>
                <td colspan="1">&nbsp;</td>
                <td colspan="4">
                    <textarea name="appraisee_summary" style="width: 98%" <?=($personal ? "" : $closed ? "disabled='disabled'" : "disabled='disabled'")?>><?=$record['appraisee_summary']?></textarea>                    
                </td>
            </tr>
        </tbody>
    </table>
    <table style="width: 100%;" border="0" class="default-table boxtype">
        <tbody>
            <tr>
                <td style="background-color: #333333;" colspan="4">
                    <strong><span style="color: #ffffff;">Part <?=$ctr++?> : Personnel Action (to be filled out by Appraiser)</span></strong>
                </td>
            </tr>    
            <tr>
                <td align="right" style="width: 5%;">[ ]</td>
                <td style="width: 1%;">&nbsp;</td>
                <td style="width: 55%;">Promotion recommended :  New Position: <?php echo form_dropdown('new_position_id',$position_array,set_value('new_position_id', $record['new_position_id']),'style="width:200px;"')?></td>
                <td style="width: 40%;">Effective On : <input type="text" size="30" row="4" id="new_position_date" value="<?=$record['new_position_date']?>" name="new_position_date" class="input-text date"></td>
            </tr>
            <tr>
                <td align="right">[ ]</td>
                <td>&nbsp;</td>
                <td>Demotion recommended:  New Position: <?php echo form_dropdown('demotion_position_id',$position_array,set_value('demotion_position_id', $record['demotion_position_id']),'style="width:200px;"')?></td>
                <td>Effective On : <input type="text" size="30" row="5" id="demotion_recommended_date" value="<?=$record['demotion_recommended_date']?>" name="demotion_recommended_date" class="input-text date"></td>
            </tr>
            <tr>
                <td align="right">[ ]</td>
                <td>&nbsp;</td>
                <td colspan="2" style="width: 95%;">Termination :  Effective On : <input type="text" size="30" row="2" id="termination_effective_date" value="<?=$record['termination_effective_date']?>" name="termination_effective_date" class="input-text date"></td>
            </tr>
            <tr>
                <td align="right">[ ]</td>
                <td>&nbsp;</td>
                <td colspan="2">Transfer :  Effective On : <input type="text" =""="" size="20" row="3" id="transfer_effective_date" value="<?=$record['transfer_effective_date']?>" name="transfer_effective_date" class="input-text date"></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td align="left" colspan="3">
                    <p>Personnel action reviewed and approved by next level supervisor.</p><br />
                    <p><input type="text" readonly="readonly" size="20" row="0" id="personal_action_signatory" value="<?= $division_head['firstname'] .' '. $division_head['lastname']?>" name="personal_action_signatory"><input type="hidden" name="division_head_id" value="<?=$division_head['user_id']?>"></p><br />
                    <p>Signature over Printed Name</p>
                </td>
            </tr>                                                                
        </tbody>
    </table>
    <table style="width: 100%;" border="0" class="default-table boxtype">
        <tbody>
            <tr>
                <td style="background-color: #333333;" colspan="4">
                    <strong><span style="color: #ffffff;">For OD Use Only</span></strong>
                </td>
            </tr>
            <tr>
                <td style="width: 3%;">&nbsp;</td>
                <td style="width: 97%;">Received and reviewed by:</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><input type="text" readonly="readonly" size="20" row="0" id="signatory" value="" name="signatory"> <br></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>Signature over Printed Name</td>
            </tr>
            <tr>
                <td align="center" colspan="2"><span style="font-size: small;"><strong>Kindly return this form to OD upon completion.</strong></span></td>
            </tr>                                                                                                                               
        </tbody>
    </table>    
</form>

<div class="clear"></div>

<div class="form-submit-btn">
    <div class="icon-label-group">
        <?php if ($appraisee->user_id != $this->userinfo['user_id']):?>
            <span>
                <div class="icon-label"> <a onclick="ajax_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> <span>Save as Draft</span> </a> </div>
                <div class="icon-label"> <a onclick="ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> <span>Save &amp; Email</span> </a> </div>
            </span>
        <?php ;elseif ($record->status != 1):?>
            <?php if ($record->status == 2):?>
            <span>
                <div class="icon-label">
                    <a href="javascript:void(0);" class="icon-16-disk icon-conforme" rel="record-save"> 
                        <span>Conforme</span>
                    </a> 
                </div>
            </span>
            <?php endif;?>
            <script type="text/javascript">
                $(document).ready(function () {                
                    $('#record-form input, #record-form textarea').attr('disabled', '');
                });
            </script>   
        <?php endif;?>        
        <div class="icon-label"> <a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list"> <span>Back to list</span> </a> </div>
        <!-- <div class="icon-label add-more-div"><a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add-more"><span>Add</span></a></div> -->
    </div>    
</div>