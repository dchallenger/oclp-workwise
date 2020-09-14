<div id="survey_container"></div>
    <div id="question-form">
        <div class="form-multiple-add" style="display: block;padding-bottom:30px;">
            <!--  <div style="width:100%;height:39px;display:block;border-bottom: 1px solid #BBB;">&nbsp;&nbsp;
                <span class="fh-delete" style="float:right;margin-left:5px;border-radius:5px;background-color:#595B5E;padding-left:15px;padding-right:15px;padding-top:5px;padding-bottom:5px;cursor:pointer;">
                    <a class="delete-detail" style ="color:#fff;" onClick="removeClone(this)" href="javascript:void(0)">DELETE</a>
                </span>
             </div> -->         

        <!-- <div class="question-form 1f" id="question-form" style="padding-top:10px;"> -->
            <!-- <div class="form-item odd" style="padding-top:10px;">
                <label class="label-desc gray" for="status[question][]">
                </label>
                <div class="text-input-wrap">Blah</div>
            </div> -->

            <input id="employee_id" name="employee_id" type="hidden" value="<?php echo $this->userinfo['user_id']; ?>">
            <input id="company_id" name="employee_id" type="hidden" value="<?php echo $this->userinfo['user_id']; ?>">
            <input id="department_id" name="employee_id" type="hidden" value="<?php echo $this->userinfo['user_id']; ?>">
    
            <div class="form-item even is_taken" style="padding-top:10px;"> 
                <label class="label-desc gray" for="status[status][]">
                </label>
                <table width="100%" style="text-align:center;">
				<th style="text-align:left">Question</th><th>&nbsp;</th><th>&nbsp;</th><th style="padding-left:20px;padding-right:20px;border: solid 1px">Not Good</th><th style="border: solid 1px">&nbsp;Good&nbsp;</th><th style="border: solid 1px">Very Good</th><th style="border: solid 1px">&nbsp;Outstanding&nbsp;</th>
                <!-- <div class="radio-input-wrap"> -->
                    <!-- <input id="status-title" class="input-radio" type="radio" value="title" name="status[]">
					<label class="check-radio-label gray" for="sex-male">Title</label>
					<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
					<label class="check-radio-label gray" for="sex-female">Question</label> -->
<!-- 					<tr><td style="text-align:left;font-style:italic">When I wear the euromoney pioneer T-Shirts...</td></tr> -->
					<?php 
					$txtctr=1;
					$txtquestion[1]="Physical Appearance of Canteen Stuff";
					$txtquestion[2]="Cleanliness and Sanitation";
					$txtquestion[3]="Customer Service";
					$txtquestion[4]="How would you assess the overall";
					$txtquestion[5]="What is your reason for item number 4";
					$txtquestion[6]="What are the areas you think they should improve on to provide better customer service";

					for($ctr=1;$ctr<=4;$ctr++):
						if($txtctr==5)
							echo '<tr><td style="text-align:left;font-style:italic">Comment</td></tr>';
						if($txtctr>=5) 
						{
							echo '<tr><td width="35%" style="text-align:left">'.$ctr.'. '.$txtquestion[$txtctr] .'</td><td colspan="9"><input id="status-question" class="input-text" type="text" name="q'. $ctr .'"></td></tr>';
						}
						else{
					?>
	
							<tr>
								<td width="35%" style="text-align:left"><?= $ctr.". ".$txtquestion[$txtctr] ?></td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="A" name="q<?= $ctr ?>">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="B" name="q<?= $ctr ?>">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="C" name="q<?= $ctr ?>">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="D" name="q<?= $ctr ?>">
								</td>
							</tr>
					<?php
				}
					$txtctr++;
					endfor;
					?>
							<!-- <tr>
								<td width="29%" style="text-align:left">2. I feel Greater Sense of Pride for Pioneer</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q2">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q2">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q2">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q2">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q2">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">3. I feel comfortable and relaxed when I wear them</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q3">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q3">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q3">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q3">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q3">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">4. I feel happy and confident about myself</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">5. I feel very professional when i'm with clients</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">6. I feel more cautious about how i carry myself because I carry the name Pioneer</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">7. I feel inspired at work</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr><td style="text-align:left;font-style:italic">When I wear the new set of corporate attire...</td></tr>
							<tr>
								<td width="35%" style="text-align:left">8. I feel Excited and Energetic</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">9. I feel Greater Sense of Pride for Pioneer</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">10. I feel comfortable and relaxed when I wear them</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">11. I feel happy and confident about myself</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">12. I feel very professional when i'm with clients</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">13. I feel more cautious about how i carry myself because I carry the name Pioneer</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">14. I feel inspired at work</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr> -->
					</table>
    	    </div>
        <!-- </div> -->
    <div class="clear"></div>
</div>