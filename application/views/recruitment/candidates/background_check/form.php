<div class="spacer"></div>
<?php  $cnt = 1;
	foreach ($references as $key => $reference):?>

<!-- Reference Details -->
<h3 class="form-head"></h3>
<table class="default-table boxtype" style="width:100%">
	<thead><th align="left"><h3>Details</h3></th></thead>
</table> 
<div class="col-2-form view ">
	<div class="form-item view odd ">
	    <label class="label-desc view gray" for="reference_name">Character Reference:</label>
	    <div class="text-input-wrap"><?=$reference->name?></div>		
	</div>
	<div class="form-item view even ">
	    <label class="label-desc view gray" for="position_id">Position:</label>
	    <div class="text-input-wrap"><?=$reference->position?></div>		
	</div>
	<div class="form-item view odd ">
	    <label class="label-desc view gray" for="email">Email Address:</label>
	    <div class="text-input-wrap"><?=$reference->email?></div>		
	</div>
	<div class="form-item view even ">
	    <label class="label-desc view gray" for="company_name">Company:</label>
	    <div class="text-input-wrap"><?=$reference->company_name?></div>		
	</div>
</div>
<!-- Reference Details -->	

<!-- Reference Questionnaire -->
<div id="fg-<?=$cnt?>" class="" fg_id="<?=$cnt?>" style="margin-left:2%">
	
	<h4 class="form-head">Reference Questionnaire
		<a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );" class="align-right other-link noborder" href="javascript:void(0)">Show</a>
	</h4>
	<div class="col-1-form hidden">
		<?php foreach ($questionnaires['questions'] as $questions => $values):?>
			<h6><?=$values?>:</h6> 
			<div class="col-1-form hidden">
			<?php  
				foreach ($questionnaires[$questions] as $key => $question):?>
				<div class="form-item odd ">
				    <label class="label-desc gray" for="question_description"><?=$question->description?>:</label>
				    <div class="text-input-wrap"><textarea class="input-text" name="questions[<?=$questions?>][<?=$reference->record_id?>][]" style="width: 100%; height: 55px;"><?=$record_questions[$questions][$reference->record_id][$key]?></textarea></div>		
				</div>
			<?php endforeach;?>
			</div>
		<?php endforeach;?>
	</div>

</div>
<!-- Reference Questionnaire -->

<div class="spacer"></div>
<?php $cnt++;
	endforeach;?>