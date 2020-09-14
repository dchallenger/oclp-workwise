<div class="form-item odd">
	<div>Using the following scale, how would you rate the Company?</div>
	<div class="spacer"></div>
	<table style="width: 100%">
		<tr>
			<td width="25%">(4) - Excellent</td>
			<td width="25%">(3) - Good</td>
			<td width="25%">(2) - Fair</td>
			<td width="25%">(1) - Poor</td>
		</tr>
	</table>
	<div class="spacer"></div>
	<div class="form-item odd">
		<div><input type="text" class="rating_input" name="rating[performance_feedback]" value="<?=isset($record['performance_feedback']) ? $record['performance_feedback'] : set_value('performance_feedback', 0)?>" />Gave performance feedback</div>
		<div><input type="text" class="rating_input" name="rating[listen_suggestion]" value="<?=isset($record['listen_suggestion']) ? $record['listen_suggestion'] : set_value('listen_suggestion', 0)?>" />Listened to suggestions</div>
		<div><input type="text" class="rating_input" name="rating[encourage_cooperation]" value="<?=isset($record['encourage_cooperation']) ? $record['encourage_cooperation'] : set_value('encourage_cooperation', 0)?>"/>Encouraged cooperation</div>
		<div><input type="text" class="rating_input" name="rating[treat_fairly]" value="<?=isset($record['treat_fairly']) ? $record['treat_fairly'] : set_value('treat_fairly', 0)?>"/>Treated you fairly</div>
		<div><input type="text" class="rating_input" name="rating[provide_leadership]" value="<?=isset($record['provide_leadership']) ? $record['provide_leadership'] : set_value('provide_leadership', 0)?>"/>Provided leadership</div>
		<div><input type="text" class="rating_input" name="rating[communicated_expectations]" value="<?=isset($record['communicated_expectations']) ? $record['communicated_expectations'] : set_value('communicated_expectations', 0)?>"/>Clearly communicated expectations</div>
	</div>
	<div class="form-item even">
		<div><input type="text" class="rating_input" name="rating[recognize_accomplishments]" value="<?=isset($record['recognize_accomplishments']) ? $record['recognize_accomplishments'] : set_value('recognize_accomplishments', 0)?>"/>Recognized accomplishments</div>
		<div><input type="text" class="rating_input" name="rating[coach_train_develop]" value="<?=isset($record['coach_train_develop']) ? $record['coach_train_develop'] : set_value('coach_train_develop', 0)?>"/>Coached, trained, and developed you</div>
		<div><input type="text" class="rating_input" name="rating[resolved_complaints]" value="<?=isset($record['resolved_complaints']) ? $record['resolved_complaints'] : set_value('resolved_complaints', 0)?>"/>Resolved concerns and complaints promptly</div>
		<div><input type="text" class="rating_input" name="rating[provide_challenge]" value="<?=isset($record['provide_challenge']) ? $record['provide_challenge'] : set_value('provide_challenge', 0)?>"/>Provided appropriate and challenging assignments</div>
		<div><input type="text" class="rating_input" name="rating[build_teamwork]" value="<?=isset($record['build_teamwork']) ? $record['build_teamwork'] : set_value('build_teamwork', 0)?>"/>Built teamwork</div>
		<div><input type="text" class="rating_input" name="rating[was_honest]" value="<?=isset($record['was_honest']) ? $record['was_honest'] : set_value('was_honest', 0)?>"/>Was honest</div>
	</div>
</div>