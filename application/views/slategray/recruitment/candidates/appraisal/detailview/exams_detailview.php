<style>
ul.exam li {
	float: left;
	padding: 0 20px 20px 0;	
}
</style>

<?php
	$exam_info = $this->input->post('exams_info');
?>

<h5>-Vocabulary & Arithmetic Reasoning</h5>
<br />
<ul class="exam">
	<li>
		<label class="label-desc gray" for="exam_var_raw_score" title="Vocabulary Raw Score">Raw Score:<span class="red font-large">*</span></label>
		<div class="text-input-wrap">
			<input id="exam_var_raw_score" class="input-text required numeric" type="text" name="exam_var_raw_score" value="<?php echo $exam_info['exam_var_raw_score'] ?>" style="width:200px">
		</div>
	</li>
	<li>
		<label class="label-desc gray" for="exam_var_percentile" title="Vocabulary Percentile">Percentile:<span class="red font-large">*</span></label>
		<div class="text-input-wrap">
			<input id="exam_var_percentile" class="input-text required numeric" type="text" name="exam_var_percentile" value="<?php echo $exam_info['exam_var_percentile'] ?>" style="width:200px">
		</div>
	</li>
	<li>
		<label class="label-desc gray" for="exam_var_remarks" title="Vocabulary Remarks">Remarks:</label>
		<div class="textarea-input-wrap">
		    <textarea class="input-textarea required" id="exam_var_remarks" name="exam_var_remarks" rows="5" style="width:300px"><?php echo $exam_info['exam_var_remarks'] ?></textarea>
		</div>
	</li>`
</ul>
<br clear="left"/>
<h5>-Flexibility, vocabulary and arithmetic skills</h5>
<br />
<ul class="exam">
	<li>
		<label class="label-desc gray" for="exam_fvas_raw_score" title="Flexibility Raw Score">Raw Score:<span class="red font-large">*</span></label>
		<div class="text-input-wrap">
			<input id="exam_fvas_raw_score" class="input-text required numeric" type="text" name="exam_fvas_raw_score" value="<?php echo $exam_info['exam_fvas_raw_score'] ?>" style="width:200px">
		</div>
	</li>
	<li>
		<label class="label-desc gray" for="exam_fvas_percentile" title="Flexibility Percentile">Percentile:<span class="red font-large">*</span></label>
		<div class="text-input-wrap">
			<input id="exam_fvas_percentile" class="input-text required numeric" type="text" name="exam_fvas_percentile" value="<?php echo $exam_info['exam_fvas_percentile'] ?>" style="width:200px">
		</div>
	</li>
	<li>
		<label class="label-desc gray" for="exam_fvas_remarks" title="Flexibility Remarks">Remarks:</label>
		<div class="textarea-input-wrap">
		    <textarea class="input-textarea required" id="exam_fvas_remarks" name="exam_fvas_remarks" rows="5" style="width:300px"><?php echo $exam_info['exam_fvas_remarks'] ?></textarea>
		</div>
	</li>
</ul>
<br clear="left"/>
<h5>-Analysis of information and solve problems using visual concepts</h5>
<br />
<ul class="exam">
	<li>
		<label class="label-desc gray" for="exam_aispuvc_raw_score" title="Analysis Raw Score">Raw Score:<span class="red font-large">*</span></label>
		<div class="text-input-wrap">
			<input id="exam_aispuvc_raw_score" class="input-text required numeric" type="text" name="exam_aispuvc_raw_score" value="<?php echo $exam_info['exam_aispuvc_raw_score'] ?>" style="width:200px">
		</div>
	</li>
	<li>
		<label class="label-desc gray" for="exam_aispuvc_percentile" title="Analysis Percentile">Percentile:<span class="red font-large">*</span></label>
		<div class="text-input-wrap">
			<input id="exam_aispuvc_percentile" class="input-text required numeric" type="text" name="exam_aispuvc_percentile" value="<?php echo $exam_info['exam_aispuvc_percentile'] ?>" style="width:200px">
		</div>
	</li>
	<li>
		<label class="label-desc gray" for="exam_aispuvc_remarks" title="Analysis Remarks">Remarks:</label>
		<div class="textarea-input-wrap">
		    <textarea class="input-textarea required" id="exam_aispuvc_remarks" name="exam_aispuvc_remarks" rows="5" style="width:300px"><?php echo $exam_info['exam_aispuvc_remarks'] ?></textarea>
		</div>
	</li>
</ul>
<br clear="left"/>
<h5>-Mental alertness, judgment & comprehension & Arithmetic skills</h5>
<br />
<ul class="exam">
	<li>
		<label class="label-desc gray" for="exam_majca_raw_score" title="Mental Raw Score">Raw Score:<span class="red font-large">*</span></label>
		<div class="text-input-wrap">
			<input id="exam_majca_raw_score" class="input-text required numeric" type="text" name="exam_majca_raw_score" value="<?php echo $exam_info['exam_majca_raw_score'] ?>" style="width:200px">
		</div>
	</li>
	<li>
		<label class="label-desc gray" for="exam_majca_percentile" title="Mental Percentile">Percentile:<span class="red font-large">*</span></label>
		<div class="text-input-wrap">
			<input id="exam_majca_percentile" class="input-text required numeric" type="text" name="exam_majca_percentile" value="<?php echo $exam_info['exam_majca_percentile'] ?>" style="width:200px">
		</div>
	</li>
	<li>
		<label class="label-desc gray" for="exam_majca_remarks" title="Mental Remarks">Remarks:</label>
		<div class="textarea-input-wrap">
		    <textarea class="input-textarea required" id="exam_majca_remarks" name="exam_majca_remarks" rows="5" style="width:300px"><?php echo $exam_info['exam_majca_remarks'] ?></textarea>
		</div>
	</li>
</ul>
<br clear="left"/>
<label class="label-desc gray" for="recommendation" title="Reccommendation">Recommendation:<span class="red font-large">*</span></label>
<div class="textarea-input-wrap">
	<textarea class="input-textarea required" id="recommendation" name="recommendation" rows="5" style="width:760px"><?php echo $exam_info['recommendation'] ?></textarea>
</div>
<div>
	<input type="hidden" class="" name="screening_datetime" id="screening_datetime_post" />
	<input type="hidden" class="" name="interviewer_id" id="interviewer_id_post" />
	<input type="hidden" class="" name="final_interviewer_id" id="final_interviewer_id_post" />
	<input type="hidden" class="" name="final_datetime" id="final_datetime_post" />
</div>