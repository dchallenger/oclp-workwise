<input type="hidden" name="calendar_id" id="calendar_id" value="<?= $calendar_id ?>" />
<input type="hidden" name="employee_direct" id="employee_direct" value="<?= $employee_direct ?>" />
<table id="module-participant" style="width:100%;" class="default-table boxtype">
    <tbody>
	<?php 
		if( $feedback_questionnaire_item_count > 0 ){

			$current_score_type = 0;
			$current_feedback_category = 0;

			foreach( $feedback_questionnaire_items as $questionnaire_info ){

				if( $current_feedback_category != $questionnaire_info['feedback_category_id'] ){

					?>

					<tr>
				        <th style="vertical-align:middle; text-align:left; font-weight:bold;" colspan="7" class="odd">
				        	<?= $questionnaire_info['feedback_category'] ?> )
				        </th>
				    </tr>

					<?php

					$current_score_type = 0;


				}

				if( $questionnaire_info['score_type'] == 1 ){ // 5-point scale

					if( $current_score_type == $questionnaire_info['score_type'] ){
					?>
						<tr>
				            <td style="text-align:left; vertical-align:top;" colspan="2"><?= $questionnaire_info['feedback_item'] ?></td>
				            <td style="text-align:center;"><input type="radio" value="1" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 1 ){ echo "checked"; } ?> disabled=""/></td>
				            <td style="text-align:center;"><input type="radio" value="2" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 2 ){ echo "checked"; } ?> disabled=""/></td>
				            <td style="text-align:center;"><input type="radio" value="3" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 3 ){ echo "checked"; } ?> disabled=""/></td>
				            <td style="text-align:center;"><input type="radio" value="4" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 4 ){ echo "checked"; } ?> disabled=""/></td>
				            <td style="text-align:center;"><input type="radio" value="5" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?> disabled=""/></td>
				        </tr>
					<?php
					}
					else{
					?>
						<tr>
					        <th style="vertical-align:middle; width:40%;" class="odd" colspan="2">
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Strongly Disagree
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Disagree
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Neutral
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Agree
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Strongly Agree
					        </th>
					    </tr>
				    	<tr>
 							<td style="text-align:left; vertical-align:top;" colspan="2"><?= $questionnaire_info['feedback_item'] ?></td>
				            <td style="text-align:center;"><input type="radio" value="1" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 1 ){ echo "checked"; } ?> disabled=""/></td>
				            <td style="text-align:center;"><input type="radio" value="2" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 2 ){ echo "checked"; } ?> disabled=""/></td>
				            <td style="text-align:center;"><input type="radio" value="3" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 3 ){ echo "checked"; } ?> disabled=""/></td>
				            <td style="text-align:center;"><input type="radio" value="4" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 4 ){ echo "checked"; } ?> disabled=""/></td>
				            <td style="text-align:center;"><input type="radio" value="5" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?> disabled=""/></td>
				        </tr>
					<?php
					}

				} 
				elseif( $questionnaire_info['score_type'] == 2 ){ // Yes or No

					if( $current_score_type == $questionnaire_info['score_type'] ){
					?>
						<tr>
				            <td style="text-align:left; vertical-align:top;" colspan="2"><?= $questionnaire_info['feedback_item'] ?></td>
				            <td style="text-align:left;"><input type="radio" value="5" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?> disabled="" />Yes</td>
				            <td style="text-align:left;" colspan="4"><input type="radio" value="0" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 0 ){ echo "checked"; } ?> disabled="" />No</td>
				        </tr>
					<?php
					}
					else{
					?>
						<tr>
					        <th style="vertical-align:middle; width:100%;" colspan="7" class="odd"></th>
					    </tr>
				    	<tr>
				            <td style="text-align:left; vertical-align:top;" colspan="2"><?= $questionnaire_info['feedback_item'] ?></td>
				            <td style="text-align:left;"><input type="radio" value="5" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?> disabled="" />Yes</td>
				            <td style="text-align:left;" colspan="4"><input type="radio" value="0" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 0 ){ echo "checked"; } ?> disabled="" />No</td>
				        </tr>
				    <?php
					}

				}
				elseif( $questionnaire_info['score_type'] == 3 ){ // Essay

					if( $current_score_type == $questionnaire_info['score_type'] ){
					?>
						<tr>
				            <td style="text-align:left; vertical-align:top;"><?= $questionnaire_info['feedback_item'] ?></td>
				            <td style="text-align:left;" colspan="6"><textarea style="width:100%;" disabled="" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]"><?= $questionnaire_info['remarks'] ?></textarea></td>
				        </tr>
					<?php
					}
					else{
					?>
						<tr>
					        <th style="vertical-align:middle; width:100%;" colspan="7" class="odd"></th>
					    </tr>
				    	<tr>
				            <td style="text-align:left; vertical-align:top;"><?= $questionnaire_info['feedback_item'] ?></td>
				             <td style="text-align:left;" colspan="6"><textarea style="width:100%;" disabled="" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]"><?= $questionnaire_info['remarks'] ?></textarea></td>
				        </tr>
					<?php
					}

				}
				elseif( $questionnaire_info['score_type'] == 4 ){ // 6-point scale

					if( $current_score_type == $questionnaire_info['score_type'] ){
					?>
						<tr>
				            <td style="text-align:left; vertical-align:top;"><?= $questionnaire_info['feedback_item'] ?></td>
				            <td style="text-align:center;"><input type="radio" value="0" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 0 ){ echo "checked"; } ?> disabled="" /></td>
				            <td style="text-align:center;"><input type="radio" value="1" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 1 ){ echo "checked"; } ?> disabled="" /></td>
				            <td style="text-align:center;"><input type="radio" value="2" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 2 ){ echo "checked"; } ?> disabled="" /></td>
				            <td style="text-align:center;"><input type="radio" value="3" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 3 ){ echo "checked"; } ?> disabled="" /></td>
				            <td style="text-align:center;"><input type="radio" value="4" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 4 ){ echo "checked"; } ?> disabled="" /></td>
				            <td style="text-align:center;"><input type="radio" value="5" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?> disabled="" /></td>
				        </tr>
					<?php
					}
					else{
					?>
						<tr>
					        <th style="vertical-align:middle; width:40%;" class="odd">
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Not Much
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Basic
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Average
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Good
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Very Good
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Excellent
					        </th>
					    </tr>
				    	<tr>
				            <td style="text-align:left; vertical-align:top;"><?= $questionnaire_info['feedback_item'] ?></td>
				            <td style="text-align:center;"><input type="radio" value="0" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 0 ){ echo "checked"; } ?> disabled="" /></td>
				            <td style="text-align:center;"><input type="radio" value="1" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 1 ){ echo "checked"; } ?> disabled="" /></td>
				            <td style="text-align:center;"><input type="radio" value="2" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 2 ){ echo "checked"; } ?> disabled="" /></td>
				            <td style="text-align:center;"><input type="radio" value="3" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 3 ){ echo "checked"; } ?> disabled="" /></td>
				            <td style="text-align:center;"><input type="radio" value="4" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 4 ){ echo "checked"; } ?> disabled="" /></td>
				            <td style="text-align:center;"><input type="radio" value="5" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?> disabled="" /></td>
				        </tr>
					<?php
					}

				} 
				elseif( $questionnaire_info['score_type'] == 5 ){ // 4-point scale

					if( $current_score_type == $questionnaire_info['score_type'] ){
					?>
						<tr>
				            <td style="text-align:left; vertical-align:top;" colspan="3"><?= $questionnaire_info['feedback_item'] ?></td>
				            <td style="text-align:center;"><input type="radio" value="1.25" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 1.25 ){ echo "checked"; } ?> disabled=""/></td>
				            <td style="text-align:center;"><input type="radio" value="2.5" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 2.5 ){ echo "checked"; } ?> disabled=""/></td>
				            <td style="text-align:center;"><input type="radio" value="3.75" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 3.75 ){ echo "checked"; } ?> disabled=""/></td>
				            <td style="text-align:center;"><input type="radio" value="5" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?> disabled=""/></td>
				        </tr>
					<?php
					}
					else{
					?>
						<tr>
					        <th style="vertical-align:middle; width:40%;" class="odd" colspan="3">
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Unsatisfactory
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Needs improvement
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Meets requirements
					        </th>
					        <th style="vertical-align:middle; width:12%;" class="odd">
					        	Excellent
					        </th>
					    </tr>
				    	<tr>
 							<td style="text-align:left; vertical-align:top;" colspan="3"><?= $questionnaire_info['feedback_item'] ?></td>
				            <td style="text-align:center;"><input type="radio" value="1.25" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 1.25 ){ echo "checked"; } ?> disabled=""/></td>
				            <td style="text-align:center;"><input type="radio" value="2.5" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 2.5 ){ echo "checked"; } ?> disabled=""/></td>
				            <td style="text-align:center;"><input type="radio" value="3.75" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 3.75 ){ echo "checked"; } ?> disabled=""/></td>
				            <td style="text-align:center;"><input type="radio" value="5" class="feedback_average" name="feedback_item[<?= $questionnaire_info['feedback_item_id'] ?>]" <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?> disabled=""/></td>
				        </tr>
					<?php
					}

				} 

				$current_score_type = $questionnaire_info['score_type'];
				$current_feedback_category = $questionnaire_info['feedback_category_id'];

			}
		}
	?>
    </tbody>
</table>

<div >
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
</div>