<!-- <input type="hidden" name="calendar_id" id="calendar_id" value="<?= $calendar_id ?>" /> -->
<!-- <input type="hidden" name="employee_direct" id="employee_direct" value="<?= $employee_direct ?>" /> -->
<table id="module-participant" style="width:100%;" class="default-table boxtype">
    <tbody>
	<?php 
		if( $clearance_exit_interview_questionnaire_item_count > 0 ){

			$current_exit_interview_score_type = 0;
			$current_clearance_exit_interview_category = 0;

			foreach( $clearance_exit_interview_questionnaire_items as $questionnaire_info ){

				if( $current_clearance_exit_interview_category != $questionnaire_info['clearance_exit_interview_category_id'] ){

					?>

					<tr>
				        <th style="vertical-align:middle; text-align:left; font-weight:bold;" colspan="7" class="odd">
				        	( <?= $questionnaire_info['clearance_exit_interview_category'] ?> )
				        </th>
				    </tr>

					<?php

					$current_exit_interview_score_type = 0;

				}
				if( $questionnaire_info['exit_interview_score_type'] == 1 ){ // 4-point scale

					if( $current_exit_interview_score_type == $questionnaire_info['exit_interview_score_type'] ){
					?>
						<tr>
				            <td style="text-align:left; vertical-align:top;" colspan="3"><?= $questionnaire_info['clearance_exit_interview_item'] ?></td>
				            <td style="text-align:center;"><input type="radio" value="1" class="clearance_exit_interview_average" name="clearance_exit_interview_item[<?= $questionnaire_info['clearance_exit_interview_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 1 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="2" class="clearance_exit_interview_average" name="clearance_exit_interview_item[<?= $questionnaire_info['clearance_exit_interview_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 2 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="3" class="clearance_exit_interview_average" name="clearance_exit_interview_item[<?= $questionnaire_info['clearance_exit_interview_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 3 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="4" class="clearance_exit_interview_average" name="clearance_exit_interview_item[<?= $questionnaire_info['clearance_exit_interview_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 4 ){ echo "checked"; } ?>/></td>
				        </tr>
					<?php
					}
					else{
					?>
						<tr>
					        <th style="vertical-align:middle; width:40%;" class="odd" colspan="3">
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Not at All
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Small Degree
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Moderate Degree
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	High Degree
					        </th>
					    </tr>
				    	<tr>
 							<td style="text-align:left; vertical-align:top;" colspan="3"><?= $questionnaire_info['clearance_exit_interview_item'] ?></td>
				            <td style="text-align:center;"><input type="radio" value="1" class="clearance_exit_interview_average" name="clearance_exit_interview_item[<?= $questionnaire_info['clearance_exit_interview_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 1 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="2" class="clearance_exit_interview_average" name="clearance_exit_interview_item[<?= $questionnaire_info['clearance_exit_interview_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 2 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="3" class="clearance_exit_interview_average" name="clearance_exit_interview_item[<?= $questionnaire_info['clearance_exit_interview_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 3 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="4" class="clearance_exit_interview_average" name="clearance_exit_interview_item[<?= $questionnaire_info['clearance_exit_interview_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 4 ){ echo "checked"; } ?>/></td>
				        </tr>
					<?php
					}

				} 

				$current_exit_interview_score_type = $questionnaire_info['exit_interview_score_type'];
				$current_clearance_exit_interview_category = $questionnaire_info['clearance_exit_interview_category_id'];

			}
		}
	?>
    </tbody>
</table>

<!-- <div >
    <fieldset>
        <div class="form-item odd">
            <label class="label-desc gray" for="date">Total Score:</label>
            <div class="text-input-wrap">               
                <input type="text" class="input-text total_score" style="width:20%;" value="<?= $total_score ?>" readonly="" name="total_score">
            </div>                                    
        </div>
        <div class="form-item odd">
            <label class="label-desc gray" for="date">Average:</label>
            <div class="text-input-wrap">               
                <input type="text" class="input-text average" style="width:20%;" value="<?= $average_score ?>" readonly="" name="average_score">&#037;
            </div>                                    
        </div>
    </fieldset>
</div> -->