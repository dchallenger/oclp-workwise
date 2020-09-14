     <style type="text/css">
     table tr td {
        vertical-align:top;
        /*text-align: center;*/
        }
</style>
<?php 
    $ratings = $dropdowns['rating'];
    $areas_developments = $dropdowns['appraisal_areas_development'];
    $learnings = $dropdowns['learning_mode'];
    $competencies = $dropdowns['competencies'];
    $development_categorys = $dropdowns['appraisal_development_category'];
    $budget_allocation = $dropdowns['budget_allocation'];
?>
     <table style="width: 100%;" border="0" class="default-table boxtype valign">
            <tbody>

                <tr>
                    <td colspan="8" style="font-size:13px;text-align: left;" >The performance development plan discussion provides an opportunity to identify the employee's development needs. In areas where improvement or growth can be made, the team member and coach need to make specific plans and commitments.</td>
                </tr>     
                <tr><td colspan="8">&nbsp;</td></tr> 
                <tr>
                    <!-- <th style="text-align: left;"><strong>% Distribution</strong></th> -->
                    <th style="text-align: left;"><strong>Areas for Development</strong></th>
                    <!-- <th style="text-align: left;"><strong>Rating</strong></th> -->
                    <th style="text-align: left;"><strong>Learning Mode</strong></th>
                    <th style="text-align: left;"><strong>Competencies</strong></th>
                    <th style="text-align: left;"><strong>Development Category</strong></th>
                    <th style="text-align: left;"><strong>Topic</strong></th>
                    <th style="text-align: left;"><strong>Remarks</strong></th>
                    <th style="text-align: left;"><strong>&nbsp;</strong></th>                      
                </tr>

                <?php foreach ($idp_details['percent_distribution'] as $idp_key => $percent_distribution):?>  

                    <tr class="idp-additional">  
                        <!-- <td><?=$percent_distribution?></td> -->
                        <td><?php foreach ($areas_developments as $areas_development):?>
                                <?=($idp_details['areas_development'][$idp_key] == $areas_development->appraisal_areas_development_id) ? $areas_development->appraisal_areas_development : ''?>     
                            <?php endforeach;?>
                        </td>
<!--                         <td><?php foreach ($ratings as $rating):?>
                                <?=($idp_details['rating'][$idp_key] == $rating->rating) ? $rating->rating .' - '. $rating->definition_rating : ''?>     
                            <?php endforeach;?>
                        </td> -->
                        <td><?php foreach ($learnings as $learning):?>
                                <?=($idp_details['learning_mode'][$idp_key] == $learning->learning_mode_id) ? $learning->learning_mode : ''?>
                            <?php endforeach;?>
                        </td>
                        <td><?php foreach ($competencies as $competency):?>
                                <?=($idp_details['competencies'][$idp_key] == $competency->training_category_id) ? $competency->training_category : ''?>
                            <?php endforeach;?>
                        </td>
                        <td>
                            <?php foreach ($development_categorys as $development_category):?>
                                <?=($idp_details['development_category'][$idp_key] == $development_category->appraisal_development_category_id) ? $development_category->appraisal_development_category : ''?>
                            <?php endforeach;?>                         
                        </td>
                        <td><?=$idp_details['topic'][$idp_key]?></td>
                        <td><?=$idp_details['remarks'][$idp_key]?></td>
                        <td>&nbsp;</td>
                    </tr> 
                <?php endforeach;?>   
         
                  </tbody>
        </table>