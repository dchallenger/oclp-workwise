
<?php 


		if( $competency_questionnaire_item_count > 0 ){

			$current_score_type = 0;
			$current_position_skills_id = 0;
			$current_position_skills_weight = 0;
			$calculate_average = 0;
			$subtotal_count = 0;
			$subtotal_score = 0;
			$subtotal_average = 0;
			$non_weight_questionaire_items = $competency_questionnaire_items;

			foreach( $competency_questionnaire_items as $questionnaire_info ){

				if( $current_position_skills_id != $questionnaire_info['position_skills_id'] ){

					$current_score_type = 0;

					if( $subtotal_count > 0 ){

						$subtotal_average = $calculate_average;
						$subtotal_average = number_format($subtotal_average * 100,2);

					?>

						<tr>
							<td colspan="7" style="background-color:#fff;">
								<fieldset>
							        <div style="float:left; width:200px;">
							            <label class="label-desc gray" for="date">Total Score:</label>
							            <div class="text-input-wrap">               
							                <input type="text" class="input-text subtotal_score subtotal_score_<?= $current_position_skills_id; ?>" position-skill-id="<?= $current_position_skills_id; ?>" style="width:50%;" value="<?= $subtotal_score; ?>" readonly="" name="subtotal_score">
							            </div>                                    
							        </div>
							        <div style="float:left; width:200px;">
							            <label class="label-desc gray" for="date">Average:</label>
							            <div class="text-input-wrap">               
							                <input type="text" class="input-text sub_average sub_average_<?= $current_position_skills_id; ?>" position-skill-id="<?= $current_position_skills_id; ?>" style="width:50%;" value="<?= $subtotal_average; ?>" readonly="" name="sub_average_score">
							            	<input type="hidden" class="subtotal_count_<?= $current_position_skills_id; ?>" name="subtotal_count" value="<?= $subtotal_count; ?>" />
							            </div>                                    
							        </div>
							    </fieldset>
							</td>
						</tr>

					<?php

					}

					$subtotal_score = 0;
					$subtotal_average = 0;
					$subtotal_count = 0;
					$calculate_average = 0;

					$current_position_skills_id = $questionnaire_info['position_skills_id'];



					$total_score_items = 0;
					$non_weight_score_type = 0;


					foreach( $non_weight_questionaire_items as $non_weight_questionaire_info ){

						if( $non_weight_questionaire_info['position_skills_id'] == $current_position_skills_id ){

							if( $non_weight_questionaire_info['score_type'] == 6 || $non_weight_questionaire_info['score_type'] == 3 ){

								$non_weight_score_type++;

							}

							$total_score_items++;

						}

					}

					



					?>
						<tr>
					        <th style="vertical-align:middle; text-align:left; font-weight:bold;" colspan="7" class="odd">
					        	<?= $questionnaire_info['position_skills'] ?><?php if( $total_score_items != $non_weight_score_type ){ ?>&nbsp;( <?= $questionnaire_info['weight'] ?> &#037; )<?php } ?>
					        	<input type="hidden" class="item_weight_<?= $questionnaire_info['position_skills_id'] ?>" name="item_weight" value="<?= $questionnaire_info['weight'] ?>" />
					        </th>
					    </tr>

					<?php

				}

				if( $questionnaire_info['score_type'] == 1 ){

					if( $current_score_type == $questionnaire_info['score_type'] ){
					?>
						<tr>
				            <td style="text-align:left; vertical-align:top;" colspan="2">
				            	<?= $questionnaire_info['skills_item'] ?>&nbsp;( <?= $questionnaire_info['item_weight'] ?> &#037; )
				            	<input type="hidden" name="skills_item_weight_<?= $questionnaire_info['skills_item_id'] ?>" class="skills_item_weight" value="<?= $questionnaire_info['item_weight'] ?>" />
				            </td>
				            <td style="text-align:center;"><input type="radio" value="1" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 1 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="2" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 2 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="3" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 3 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="4" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 4 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="5" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?>/></td>
				        </tr>
					<?php
					}
					else{
					?>
						<tr>
					        <td style="vertical-align:middle; width:40%; text-align:center; font-weight:bold; background-color:#F3F3F3;" colspan="2" class="odd">
					        </td>
					        <td style="vertical-align:middle; width:12%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        	Strongly Disagree
					        </td>
					        <td style="vertical-align:middle; width:12%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        	Disagree
					        </td>
					        <td style="vertical-align:middle; width:12%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        	Neutral
					        </td>
					        <td style="vertical-align:middle; width:12%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        	Agree
					        </td>
					        <td style="vertical-align:middle; width:12%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        	Strongly Agree
					        </td>
					    </tr>
				    	<tr>
 							<td style="text-align:left; vertical-align:top;" colspan="2"><?= $questionnaire_info['skills_item'] ?>&nbsp;( <?= $questionnaire_info['item_weight'] ?> &#037; )
 								<input type="hidden" name="skills_item_weight_<?= $questionnaire_info['skills_item_id'] ?>" class="skills_item_weight" value="<?= $questionnaire_info['item_weight'] ?>" />
 							</td>
				            <td style="text-align:center;"><input type="radio" value="1" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 1 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="2" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 2 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="3" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 3 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="4" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?><?php if( $questionnaire_info['score'] == 4 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="5" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?>/></td>
				        </tr>
					<?php

					}

				} 
				elseif( $questionnaire_info['score_type'] == 2 ){ // Yes or No

					if( $current_score_type == $questionnaire_info['score_type'] ){
					?>
						<tr>
				            <td style="text-align:left; vertical-align:top;">
				            	<?= $questionnaire_info['skills_item'] ?>&nbsp;( <?= $questionnaire_info['item_weight'] ?> &#037; )
				            	<input type="hidden" name="skills_item_weight_<?= $questionnaire_info['skills_item_id'] ?>" class="skills_item_weight" value="<?= $questionnaire_info['item_weight'] ?>" />
				            </td>
				            <td style="text-align:left;"><input type="radio" value="5" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?>/>Yes</td>
				            <td style="text-align:left;" colspan="5"><input type="radio" value="0" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 0 ){ echo "checked"; } ?>/>No</td>
				        </tr>
					<?php
					}
					else{
					?>
						<tr>
					        <td style="vertical-align:middle; width:100%; text-align:center; font-weight:bold; background-color:#F3F3F3;" colspan="7" class="odd"></td>
					    </tr>
				    	<tr>
				            <td style="text-align:left; vertical-align:top;">
				            	<?= $questionnaire_info['skills_item'] ?>&nbsp;( <?= $questionnaire_info['item_weight'] ?> &#037; )
				            	<input type="hidden" name="skills_item_weight_<?= $questionnaire_info['skills_item_id'] ?>" class="skills_item_weight" value="<?= $questionnaire_info['item_weight'] ?>" />
				            </td>
				            <td style="text-align:left;"><input type="radio" value="5" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?>/>Yes</td>
				            <td style="text-align:left;" colspan="5"><input type="radio" value="0" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 0 ){ echo "checked"; } ?>/>No</td>
				        </tr>
				    <?php
					}


				}
				elseif( $questionnaire_info['score_type'] == 3 ){ // Essay

					if( $current_score_type == $questionnaire_info['score_type'] ){
					?>
						<tr>
				            <td style="text-align:left; vertical-align:top;">
				            	<?= $questionnaire_info['skills_item'] ?>
				            	<input type="hidden" name="skills_item_weight_<?= $questionnaire_info['skills_item_id'] ?>" class="skills_item_weight" value="<?= $questionnaire_info['item_weight'] ?>" />
				            </td>
				            <td style="text-align:left;" colspan="6"><textarea style="width:100%;" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?>><?= $questionnaire_info['remarks'] ?></textarea></td>
				        </tr>
					<?php
					}
					else{
					?>
						<tr>
					        <td style="vertical-align:middle; width:100%; text-align:center; font-weight:bold; background-color:#F3F3F3;" colspan="7" class="odd"></td>
					    </tr>
				    	<tr>
				            <td style="text-align:left; vertical-align:top;">
				            	<?= $questionnaire_info['skills_item'] ?>
				            	<input type="hidden" name="skills_item_weight_<?= $questionnaire_info['skills_item_id'] ?>" class="skills_item_weight" value="<?= $questionnaire_info['item_weight'] ?>" />
				            </td>
				             <td style="text-align:left;" colspan="6"><textarea style="width:100%;" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?>><?= $questionnaire_info['remarks'] ?></textarea></td>
				        </tr>
					<?php
					}

				}
				elseif( $questionnaire_info['score_type'] == 4 ){ // 6-point scale

					if( $current_score_type == $questionnaire_info['score_type'] ){
					?>
						<tr>
				            <td style="text-align:left; vertical-align:top;">
				            	<?= $questionnaire_info['skills_item'] ?>&nbsp;( <?= $questionnaire_info['item_weight'] ?> &#037; )
				            	<input type="hidden" name="skills_item_weight_<?= $questionnaire_info['skills_item_id'] ?>" class="skills_item_weight" value="<?= $questionnaire_info['item_weight'] ?>" />
				            </td>
				            <td style="text-align:center;"><input type="radio" value="0" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 0 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="1" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 1 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="2" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 2 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="3" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 3 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="4" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 4 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="5" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?>/></td>
				        </tr>
					<?php
					}
					else{
					?>
						<tr>
					        <td style="vertical-align:middle; width:40%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        </td>
					        <td style="vertical-align:middle; width:12%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        	Not Much
					        </td>
					        <td style="vertical-align:middle; width:12%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        	Basic
					        </td>
					        <td style="vertical-align:middle; width:12%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        	Average
					        </td>
					        <td style="vertical-align:middle; width:12%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        	Good
					        </td>
					        <td style="vertical-align:middle; width:12%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        	Very Good
					        </td>
					        <td style="vertical-align:middle; width:12%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        	Excellent
					        </td>
					    </tr>
				    	<tr>
 							<td style="text-align:left; vertical-align:top;">
 								<?= $questionnaire_info['skills_item'] ?>&nbsp;( <?= $questionnaire_info['item_weight'] ?> &#037; )
 								<input type="hidden" name="skills_item_weight_<?= $questionnaire_info['skills_item_id'] ?>" class="skills_item_weight" value="<?= $questionnaire_info['item_weight'] ?>" />
 							</td>	            
				            <td style="text-align:center;"><input type="radio" value="0" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 0 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="1" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 1 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="2" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 2 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="3" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 3 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="4" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 4 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="5" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?>/></td>
				        </tr>
					<?php
					}
					

				}
				elseif( $questionnaire_info['score_type'] == 5 ){ // 4-point scale

					if( $current_score_type == $questionnaire_info['score_type'] ){
					?>
						<tr>
				            <td style="text-align:left; vertical-align:top;" colspan="3">
				            	<?= $questionnaire_info['skills_item'] ?>&nbsp;( <?= $questionnaire_info['item_weight'] ?> &#037; )
				            	<input type="hidden" name="skills_item_weight_<?= $questionnaire_info['skills_item_id'] ?>" class="skills_item_weight" value="<?= $questionnaire_info['item_weight'] ?>" />
				            </td>
				            <td style="text-align:center;"><input type="radio" value="1.25" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 1.25 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="2.5" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 2.5 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="3.75" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 3.75 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="5" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?>/></td>
				        </tr>
					<?php
					}
					else{
					?>
						<tr>
					        <td style="vertical-align:middle; width:40%; text-align:center; font-weight:bold; background-color:#F3F3F3;"  colspan="3" class="odd">
					        </td>
					        <td style="vertical-align:middle; width:12%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        	Unsatisfactory
					        </td>
					        <td style="vertical-align:middle; width:12%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        	Needs improvement
					        </td>
					        <td style="vertical-align:middle; width:12%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        	Meets requirements
					        </td>
					        <td style="vertical-align:middle; width:12%; text-align:center; font-weight:bold; background-color:#F3F3F3;" class="odd">
					        	Excellent
					        </td>
					    </tr>
				    	<tr>
 							<td style="text-align:left; vertical-align:top;" colspan="3">
 								<?= $questionnaire_info['skills_item'] ?>&nbsp;( <?= $questionnaire_info['item_weight'] ?> &#037; )
 								<input type="hidden" name="skills_item_weight_<?= $questionnaire_info['skills_item_id'] ?>" class="skills_item_weight" value="<?= $questionnaire_info['item_weight'] ?>" />
 							</td>	            
				            <td style="text-align:center;"><input type="radio" value="1.25" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 1.25 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="2.5" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 2.5 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="3.75" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 3.75 ){ echo "checked"; } ?>/></td>
				            <td style="text-align:center;"><input type="radio" value="5" class="skills_average" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 5 ){ echo "checked"; } ?>/></td>
				        </tr>
					<?php
					}
					
				} 
				elseif( $questionnaire_info['score_type'] == 6 ){ // Multiple

					if( $current_score_type == $questionnaire_info['score_type'] ){
					?>
						<tr>
							<td colspan="7" style="text-align:left; vertical-align:top;">
								<?= $questionnaire_info['skills_item'] ?>
								<input type="hidden" name="skills_item_weight_<?= $questionnaire_info['skills_item_id'] ?>" class="skills_item_weight" value="<?= $questionnaire_info['item_weight'] ?>" />
							</td>
						</tr>
						<tr>
							<td style="text-align:left; vertical-align:top;">
								<input type="radio" value="1" class="multiple" skill-item-id="<?= $questionnaire_info['skills_item_id'] ?>" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][score]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 1 ){ echo "checked"; } ?>/>
								<?= $questionnaire_info['subcriteria']['sub_criteria1'] ?>
							</td>
				            <td style="text-align:left;" colspan="6"><textarea style="width:100%;" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][remarks1]" <?php if( $type == 'detail' || $questionnaire_info['score'] != 1 ){ echo "disabled"; } ?>><?php if( $questionnaire_info['score'] == 1 ){ echo $questionnaire_info['remarks']; } ?></textarea></td>
				        </tr>
				        <tr>
							<td style="text-align:left; vertical-align:top;">
								<input type="radio" value="2" class="multiple" skill-item-id="<?= $questionnaire_info['skills_item_id'] ?>" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][score]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 2 ){ echo "checked"; } ?>/>
								<?= $questionnaire_info['subcriteria']['sub_criteria2'] ?>
							</td>
				            <td style="text-align:left;" colspan="6"><textarea style="width:100%;" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][remarks2]" <?php if( $type == 'detail' || $questionnaire_info['score'] != 2 ){ echo "disabled"; } ?>><?php if( $questionnaire_info['score'] == 2 ){ echo $questionnaire_info['remarks']; } ?></textarea></td>
				        </tr>
				        <tr>
							<td style="text-align:left; vertical-align:top;">
								<input type="radio" value="3" class="multiple" skill-item-id="<?= $questionnaire_info['skills_item_id'] ?>" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][score]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 3 ){ echo "checked"; } ?>/>
								<?= $questionnaire_info['subcriteria']['sub_criteria3'] ?>
							</td>
				            <td style="text-align:left;" colspan="6"><textarea style="width:100%;" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][remarks3]" <?php if( $type == 'detail' || $questionnaire_info['score'] != 3 ){ echo "disabled"; } ?>><?php if( $questionnaire_info['score'] == 3 ){ echo $questionnaire_info['remarks']; } ?></textarea></td>
				        </tr>
				        <tr>
							<td style="text-align:left; vertical-align:top;">
								<input type="radio" value="4" class="multiple" skill-item-id="<?= $questionnaire_info['skills_item_id'] ?>" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][score]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 4 ){ echo "checked"; } ?>/>
								<?= $questionnaire_info['subcriteria']['sub_criteria4'] ?> 
							</td>
				            <td style="text-align:left;" colspan="6"><textarea style="width:100%;" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][remarks4]" <?php if( $type == 'detail' || $questionnaire_info['score'] != 4 ){ echo "disabled"; } ?>><?php if( $questionnaire_info['score'] == 4 ){ echo $questionnaire_info['remarks']; } ?></textarea></td>
				        </tr>
					<?php
					}
					else{
					?>
						<tr>
					        <td style="vertical-align:middle; width:100%; text-align:center; font-weight:bold; background-color:#F3F3F3;" colspan="7" class="odd"></td>
					    </tr>
				    	<tr>
							<td colspan="7" style="text-align:left; vertical-align:top;">
								<?= $questionnaire_info['skills_item'] ?> 
								<input type="hidden" name="skills_item_weight_<?= $questionnaire_info['skills_item_id'] ?>" class="skills_item_weight" value="<?= $questionnaire_info['item_weight'] ?>" />
							</td>
						</tr>
						<tr>
							<td style="text-align:left; vertical-align:top;">
								<input type="radio" value="1" class="multiple" skill-item-id="<?= $questionnaire_info['skills_item_id'] ?>" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][score]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 1 ){ echo "checked"; } ?>/>
								<?= $questionnaire_info['subcriteria']['sub_criteria1'] ?>
							</td>
				            <td style="text-align:left;" colspan="6"><textarea style="width:100%;" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][remarks1]" <?php if( $type == 'detail' || $questionnaire_info['score'] != 1 ){ echo "disabled"; } ?>><?php if( $questionnaire_info['score'] == 1 ){ echo $questionnaire_info['remarks']; } ?></textarea></td>
				        </tr>
				        <tr>
							<td style="text-align:left; vertical-align:top;">
								<input type="radio" value="2" class="multiple" skill-item-id="<?= $questionnaire_info['skills_item_id'] ?>" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][score]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 2 ){ echo "checked"; } ?>/>
								<?= $questionnaire_info['subcriteria']['sub_criteria2'] ?>
							</td>
				            <td style="text-align:left;" colspan="6"><textarea style="width:100%;" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][remarks2]" <?php if( $type == 'detail' || $questionnaire_info['score'] != 2 ){ echo "disabled"; } ?>><?php if( $questionnaire_info['score'] == 2 ){ echo $questionnaire_info['remarks']; } ?></textarea></td>
				        </tr>
				        <tr>
							<td style="text-align:left; vertical-align:top;">
								<input type="radio" value="3" class="multiple" skill-item-id="<?= $questionnaire_info['skills_item_id'] ?>" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][score]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 3 ){ echo "checked"; } ?>/>
								<?= $questionnaire_info['subcriteria']['sub_criteria3'] ?>
							</td>
				            <td style="text-align:left;" colspan="6"><textarea style="width:100%;" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][remarks3]" <?php if( $type == 'detail' || $questionnaire_info['score'] != 3 ){ echo "disabled"; } ?>><?php if( $questionnaire_info['score'] == 3 ){ echo $questionnaire_info['remarks']; } ?></textarea></td>
				        </tr>
				        <tr>
							<td style="text-align:left; vertical-align:top;">
								<input type="radio" value="4" class="multiple" skill-item-id="<?= $questionnaire_info['skills_item_id'] ?>" position-skill-id="<?= $current_position_skills_id; ?>" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][score]" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( $questionnaire_info['score'] == 4 ){ echo "checked"; } ?>/>
								<?= $questionnaire_info['subcriteria']['sub_criteria4'] ?> 
							</td>
				            <td style="text-align:left;" colspan="6"><textarea style="width:100%;" name="skills_item[<?= $questionnaire_info['skills_item_id'] ?>][remarks4]" <?php if( $type == 'detail' || $questionnaire_info['score'] != 4 ){ echo "disabled"; } ?>><?php if( $questionnaire_info['score'] == 4 ){ echo $questionnaire_info['remarks']; } ?></textarea></td>
				        </tr>
					<?php
					}

				}

				$current_score_type = $questionnaire_info['score_type'];

				if( $questionnaire_info['score_type'] != 6 && $questionnaire_info['score_type'] != 3 ){

					$subtotal_count++;
					$subtotal_score += $questionnaire_info['score'];
					$calculate_average += ( ( ( $questionnaire_info['score'] / 5 ) * ( $questionnaire_info['item_weight'] / 100 ) ) * ( $questionnaire_info['weight'] / 100 ) );
					$current_position_skills_weight = $questionnaire_info['weight'];
				
				}

				

			}

			if( $subtotal_count > 0 ){

				$subtotal_average = $calculate_average;
				$subtotal_average = number_format($subtotal_average * 100,2);


				?>

					<tr>
						<td colspan="7" style="background-color:#fff;">
							<fieldset>
						        <div style="float:left; width:200px;">
						            <label class="label-desc gray" for="date">Total Score:</label>
						            <div class="text-input-wrap">               
						                <input type="text" class="input-text subtotal_score subtotal_score_<?= $current_position_skills_id; ?>" position-skill-id="<?= $current_position_skills_id; ?>" style="width:50%;" value="<?= $subtotal_score; ?>" readonly="" name="subtotal_score">
						            </div>                                    
						        </div>
						        <div style="float:left; width:200px;">
						            <label class="label-desc gray" for="date">Average:</label>
						            <div class="text-input-wrap">               
						                <input type="text" class="input-text sub_average sub_average_<?= $current_position_skills_id; ?>" position-skill-id="<?= $current_position_skills_id; ?>" style="width:50%;" value="<?= $subtotal_average; ?>" readonly="" name="sub_average_score">
						            	<input type="hidden" class="subtotal_count_<?= $current_position_skills_id; ?>" name="subtotal_count" value="<?= $subtotal_count; ?>" />
						            </div>                                    
						        </div>
						    </fieldset>
						</td>
					</tr>


				<?php

			}


		}
	?>