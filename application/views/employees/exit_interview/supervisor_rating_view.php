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
		<div><?=isset($record['performance_feedback']) ? $record['performance_feedback'] : 0?> - Gave performance feedback</div>
		<div><?=isset($record['listen_suggestion']) ? $record['listen_suggestion'] : 0?> - Listened to suggestions</div>
		<div><?=isset($record['encourage_cooperation']) ? $record['encourage_cooperation'] : 0?> - Encouraged cooperation</div>
		<div><?=isset($record['treat_fairly']) ? $record['treat_fairly'] : 0?> - Treated you fairly</div>
		<div><?=isset($record['provide_leadership']) ? $record['provide_leadership'] : 0?> - Provided leadership</div>
		<div><?=isset($record['communicated_expectations']) ? $record['communicated_expectations'] : 0?> - Clearly communicated expectations</div>
	</div>
	<div class="form-item even">
		<div><?=isset($record['recognize_accomplishments']) ? $record['recognize_accomplishments'] : 0?> - Recognized accomplishments</div>
		<div><?=isset($record['coach_train_develop']) ? $record['coach_train_develop'] : 0?> - Coached, trained, and developed you</div>
		<div><?=isset($record['resolved_complaints']) ? $record['resolved_complaints'] : 0?> - Resolved concerns and complaints promptly</div>
		<div><?=isset($record['provide_challenge']) ? $record['provide_challenge'] : 0?> - Provided appropriate and challenging assignments</div>
		<div><?=isset($record['build_teamwork']) ? $record['build_teamwork'] : 0?> - Built teamwork</div>
		<div><?=isset($record['was_honest']) ? $record['was_honest'] : 0?> - Was honest</div>
	</div>
</div>