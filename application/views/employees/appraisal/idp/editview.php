     <style type="text/css">
     #idp-table tr td {
        vertical-align:top;
        text-align: center;
        }
</style>
<?php 
    $ratings = $dropdowns['rating'];
    $areas_developments = $dropdowns['appraisal_areas_development'];
    $learnings = $dropdowns['learning_mode'];
    $competencies = $dropdowns['competencies'];
    $development_categorys = $dropdowns['appraisal_development_category'];
    $budget_allocation = $dropdowns['budget_allocation'];
    $target_completions = $dropdowns['target_completion'];

    $disabled = ($is_hr || $is_head) ? '' : 'disabled="disabled"';

    $disabled = ''; //temporary enable all elements 2018-11-13

?>
     <table style="width: 100%;" border="0" class="default-table boxtype valign" id="idp-table">
            <tbody>

                <tr>
                    <td colspan="8" style="font-size:13px;text-align: left;">The performance development plan discussion provides an opportunity to identify the employee's development needs. In areas where improvement or growth can be made, the team member and coach need to make specific plans and commitments. <a tooltip="Add row" href="javascript:void(0)" class="icon-16-add icon-button add_row"></a></span></td>
                </tr>     
                <tr>
<!--                     <th><strong>% Distribution</strong></th> -->
                    <th><strong>Areas for Development</strong></th>
                    <!-- <th><strong>Rating</strong></th> -->
                    <th><strong>Learning Mode</strong></th>
                    <th><strong>Competencies</strong></th>
                    <th><strong>Development Category</strong></th>
                    <th><strong>Topic</strong></th>
                    <th><strong>Target Completion</strong></th>
                    <th><strong>Remarks</strong></th>
                    <th><strong>&nbsp;</strong></th> 
                      
                </tr>
                <?php if($this->input->post('record_id') != '-1'): 
                    foreach ($idp_details['areas_development'] as $idp_key => $percent_distribution):?>  

                    <tr class="idp-additional">  
                        <!-- <td><input type="text" value="<?=$percent_distribution?>" name="idp[percent_distribution][]" class="percent_distribution" style="width:150px;"></td> -->
                        <td>
                            <select name="idp[areas_development][]" class="learning_mode">
                                <option value="">Select ... </option>
                                <?php foreach ($areas_developments as $areas_development):?>
                                    <option value="<?=$areas_development->appraisal_areas_development_id?>" <?=($idp_details['areas_development'][$idp_key] == $areas_development->appraisal_areas_development_id) ? 'selected="SELECTED"' : ''?>><?=$areas_development->appraisal_areas_development?></option>
                                <?php endforeach;?>
                            </select>                            
                        <!--     <input type="text" value="" name="idp[areas_development][]" class="areas_development"> -->
                            <!-- <textarea name="idp[areas_development][]" class="areas_development" ><?=$idp_details['areas_development'][$idp_key]?></textarea> -->
                        </td>
<!--                         <td>
                            <select name="idp[rating][]" class="rating" style="width:100%">
                                    <option value="0">Select ... </option>
                                <?php foreach ($ratings as $rating):
                                    if ($idp_details['rating'][$idp_key] == $rating->rating) {
                                         $criteria_standard = $rating->criteria_standard;
                                    }
                                ?>
                                    <option criteria="<?=$rating->criteria_standard?>" value="<?=$rating->rating?>" <?=($idp_details['rating'][$idp_key] == $rating->rating) ? 'selected="SELECTED"' : ''?> ><?=$rating->rating .' - '. $rating->definition_rating?></option>
                                <?php endforeach;?>
                            </select>
                                <div>
                                    <small style="display: inline-block; vertical-align: middle;text-align:left" class="criteria_standard"><?=$criteria_standard?></small>
                                </div>
                        </td> -->
                        <td>
                            <select name="idp[learning_mode][]" class="learning_mode">
                                <option value="">Select ... </option>
                                <?php foreach ($learnings as $learning):?>
                                    <option value="<?=$learning->learning_mode_id?>" <?=($idp_details['learning_mode'][$idp_key] == $learning->learning_mode_id) ? 'selected="SELECTED"' : ''?>><?=$learning->learning_mode?></option>
                                <?php endforeach;?>
                            </select>
                        </td>
                        <td>
                            <select name="idp[competencies][]" class="competencies" <?=$disabled?> >
                                <option value="">Select ... </option>
                                <?php foreach ($competencies as $competency):?>
                                    <option value="<?=$competency->training_category_id?>" <?=($idp_details['competencies'][$idp_key] == $competency->training_category_id) ? 'selected="SELECTED"' : ''?>><?=$competency->training_category?></option>
                                <?php endforeach;?>
                            </select>
                        </td>
                        <td>
                            <select name="idp[development_category][]" class="budget_allocation" <?=$disabled?>>
                                <option value="">Select ... </option>
                                <?php foreach ($development_categorys as $development_category):?>
                                    <option value="<?=$development_category->appraisal_development_category_id?>" <?=($idp_details['development_category'][$idp_key] == $development_category->appraisal_development_category_id) ? 'selected="SELECTED"' : ''?> ><?=$development_category->appraisal_development_category ?></option>
                                <?php endforeach;?>
                            </select>
                        </td>
                        <td><input type="text" value="<?=$idp_details['topic'][$idp_key]?>" name="idp[topic][]" class="topic" style="width:150px;"></td>
                        <td>
                            <select name="idp[target_completion][]" class="budget_allocation" <?=$disabled?>>
                                <option value="">Select ... </option>
                                <?php foreach ($target_completions as $target_completion):?>
                                    <option value="<?=$target_completion->target_completion_id?>" <?=($idp_details['target_completion'][$idp_key] == $target_completion->target_completion_id) ? 'selected="SELECTED"' : ''?> ><?=$target_completion->target_completion ?></option>
                                <?php endforeach;?>
                            </select>
                        </td>                        
                        <td><textarea name="idp[remarks][]" class="remarks" <?=$disabled?> ><?=$idp_details['remarks'][$idp_key]?></textarea></td>
                        <td>
                            <span style="vertical-align: middle;" class="del-button">
                                <a class="icon-16-delete icon-button delete_row" href="javascript:void(0)" tooltip="Delete row" original-title=""></a>
                            </span>
                        </td>
                    </tr> 
                <?php
                    endforeach;
                    else:?>
                    <tr class="idp-additional">  
                        <!-- <td><input type="text" value="20" name="idp[percent_distribution][]" class="percent_distribution" style="width:150px;"></td> -->
                        <td>
                            <select name="idp[areas_development][]" class="learning_mode">
                                <option value="">Select ... </option>
                                <?php foreach ($areas_developments as $areas_development):?>
                                    <option value="<?=$areas_development->appraisal_areas_development_id?>"><?=$areas_development->appraisal_areas_development?></option>
                                <?php endforeach;?>
                            </select>                             
                            <!-- <textarea name="idp[areas_development][]" class="areas_development" ></textarea> -->
                        </td>
<!--                         <td>
                            <select name="idp[rating][]" class="rating" style="width:100%">
                                    <option value="0">Select ... </option>
                                <?php foreach ($ratings as $rating):?>
                                    <option value="<?=$rating->rating?>" criteria="<?=$rating->criteria_standard?>"><?=$rating->rating .' - '. $rating->definition_rating?></option>
                                <?php endforeach;?>
                            </select>
                            <div>
                                <small style="display: inline-block; vertical-align: middle;text-align:left" class="criteria_standard"></small>
                            </div>
                        </td> -->
                        <td>
                            <select name="idp[learning_mode][]" class="learning_mode">
                                <option value="">Select ... </option>
                                <?php foreach ($learnings as $learning):?>
                                    <option value="<?=$learning->learning_mode_id?>"><?=$learning->learning_mode?></option>
                                <?php endforeach;?>
                            </select>
                        </td>
                        <td>
                            <select name="idp[competencies][]" class="competencies" <?=$disabled?>>
                                <option value="">Select ... </option>
                                <?php foreach ($competencies as $competency):?>
                                    <option value="<?=$competency->training_category_id?>"><?=$competency->training_category?></option>
                                <?php endforeach;?>
                            </select>
                        </td>
                        <td>
                            <select name="idp[development_category][]" class="budget_allocation" <?=$disabled?>>
                                <option value="">Select ... </option>
                                <?php foreach ($development_categorys as $development_category):?>
                                    <option value="<?=$development_category->appraisal_development_category_id?>"><?=$development_category->appraisal_development_category?></option>
                                <?php endforeach;?>
                            </select>
                        </td>
                        <td><input type="text" value="<?=$topic[$idp_key]?>" name="idp[topic][]" class="topic" style="width:150px;"></td>
                        <td>
                            <select name="idp[target_completion][]" class="budget_allocation" <?=$disabled?>>
                                <option value="">Select ... </option>
                                <?php foreach ($target_completions as $target_completion):?>
                                    <option value="<?=$target_completion->target_completion_id?>"><?=$target_completion->target_completion ?></option>
                                <?php endforeach;?>
                            </select>
                        </td>                                                
                        <td><textarea name="idp[remarks][]" class="remarks" <?=$disabled?> > </textarea></td>
                        <td>
                            <span style="vertical-align: middle;" class="hidden del-button">
                                <a class="icon-16-delete icon-button delete_row" href="javascript:void(0)" tooltip="Delete row" original-title=""></a>
                            </span>
                        </td>
                    </tr> 
                <?php endif;?>
                <tr id="idp-additional"><td colspan="8">&nbsp;</td></tr>
                
                <!-- additional -->
                <tr id="additional" class="hide">  
                    <!-- <td><input type="text" value="" name="idp[percent_distribution][]" class="percent_distribution"></td> -->
                    <td>
                        <select name="idp[areas_development][]" class="learning_mode">
                            <option value="">Select ... </option>
                            <?php foreach ($areas_developments as $areas_development):?>
                                <option value="<?=$areas_development->appraisal_areas_development_id?>"><?=$areas_development->appraisal_areas_development?></option>
                            <?php endforeach;?>
                        </select>                        
                    </td>
<!--                     <td>
                        <select name="idp[rating][]" class="rating" style="width:100%">
                                <option value="0">Select ... </option>
                            <?php foreach ($ratings as $rating):?>
                                <option  criteria="<?=$rating->criteria_standard?>" value="<?=$rating->rating?>"><?=$rating->rating .' - '. $rating->definition_rating?></option>
                            <?php endforeach;?>
                        </select>
                        <div>
                            <small style="display: inline-block; vertical-align: middle;text-align:left" class="criteria_standard"></small>
                        </div>
                    </td> -->
                    <td>
                        <select name="idp[learning_mode][]" class="learning_mode">
                            <option value="">Select ... </option>
                            <?php foreach ($learnings as $learning):?>
                                <option value="<?=$learning->learning_mode_id?>"><?=$learning->learning_mode?></option>
                            <?php endforeach;?>
                        </select>
                    </td>
                    <td>
                        <select name="idp[competencies][]" class="competencies" <?=$disabled?> >
                            <option value="">Select ... </option>
                            <?php foreach ($competencies as $competency):?>
                                <option value="<?=$competency->training_category_id?>"><?=$competency->training_category?></option>
                            <?php endforeach;?>
                        </select>
                    </td>
                    <td>
                        <select name="idp[development_category][]" class="budget_allocation" <?=$disabled?>>
                            <option value="">Select ... </option>
                            <?php foreach ($development_categorys as $development_category):?>
                                <option value="<?=$development_category->appraisal_development_category_id?>"><?=$development_category->appraisal_development_category?></option>
                            <?php endforeach;?>
                        </select>
                    </td>
                    <td><input type="text" value="<?=$topic[$idp_key]?>" name="idp[topic][]" class="topic" style="width:150px;"></td>
                    <td>
                        <select name="idp[target_completion][]" class="budget_allocation" <?=$disabled?>>
                            <option value="">Select ... </option>
                            <?php foreach ($target_completions as $target_completion):?>
                                <option value="<?=$target_completion->target_completion_id?>"><?=$target_completion->target_completion ?></option>
                            <?php endforeach;?>
                        </select>
                    </td>                                                                    
                    <td><textarea name="idp[remarks][]" class="remarks" <?=$disabled?> > </textarea></td>
                    <td>
                        <span style="vertical-align: middle;" class="del-button">
                            <a class="icon-16-delete icon-button delete_row" href="javascript:void(0)" tooltip="Delete row" original-title=""></a>
                        </span>
                    </td>
                </tr>      
         
                  </tbody>
        </table>