<?php $ci =& get_instance();?>

<form id="export-form" method="post" action="<?=site_url('admin/export_query/export')?>">
	<input type="hidden" name="<?=$ci->key_field?>" value="<?=${$ci->key_field}?>"/>	
	<input type="hidden" name="criteria" value="<?=$description?>"/>
	<input type="hidden" name="export_query_id" value="<?=$export_query_id?>"/>
	<div id="form-div">
		<h3 class="form-head">Export</h3>
		<div class="col-1-form">
			<div class="form-item view odd">
				<label class="label-desc view gray" for="machine_operated">Description:</label>
				<div class="text-input-wrap"><?=$description?></div>
			</div>	
			<span class="radio-input-wrap">
					<input id="filter_export-fields" class="input-radio" type="radio" checked="checked" value="0" name="filter_export">
					<label class="check-radio-label gray" for="filter_export-fields">Fields</label>
					<input id="filter_export-record" class="input-radio" type="radio" value="1" name="filter_export">
					<label class="check-radio-label gray" for="filter_export-record">Array Tabs</label>
			</span>
			<div class="form-item even">
				<div class="spacer"></div>
				<label class="label-desc gray" for="filter">Filter by: </label>
			</div>
			<div class="form-item odd">
				<label class="label-desc gray" for="employees">Employees: </label>
				<div class="multiselect-input-wrap">
					<select id="multiselect-employees" multiple="multiple" class="multi-select" name="employees[]">
						<?php
		                    foreach($users as $user_info){
		                        print '<option value="'.$user_info->user_id.'">'.$user_info->firstname.' '.$user_info->lastname.'</option>';
		                    }	
	                    ?>					
					</select>
				</div>
			</div>
			<div class="form-item even">
				<div class="spacer"></div>
				<label class="label-desc gray" for="fields">Fields: </label>
				<div class="multiselect-input-wrap">
					<select id="multiselect-fields" multiple="multiple" class="multi-select" name="fields[]">
						<?php
		                    foreach($fields as $key => $val){
		                        print '<option value="'.$key.'">'.$val.'</option>';
		                    }	
	                    ?>					
					</select>
				</div>
			</div>
			<div class="form-item odd hidden" style="width:300px;">
				<div class="spacer"></div>
				<label class="label-desc gray" for="records-from">Records From:</label>
				<div class="select-input-wrap">
					<select id="records-from" name="records_from">
						<option value="">Please Select...</option>
						<option value="accountabilities">Accountabilities</option>
						<option value="affiliation">Affiliation</option>
						<option value="character_reference">Character Reference</option>
						<option value="education">Education</option>	
						<option value="employee_trainings">Employee Trainings</option>
						<option value="employment_history">Employment History</option>
						<option value="family">Family</option>	
						<option value="other_information">Other Information</option>
						<option value="skill">Skill</option>
						<option value="test_profile">Test Profile</option>
					</select>
				</div>
			</div>
			<div class="form-item even hidden">
				<label class="label-desc gray" for="records-fields">Records Fields:</label>
				<div class="multiselect-input-wrap">
					<select id="multiselect-recordfields" name="records_fields[]" multiple="multiple" class="multi-select">
					</select>
				</div>
			</div>
			<div class="form-item even" style="width:320px">
				<div class="spacer"></div>
				<label class="label-desc gray" for="fields">Type: </label>
				<div class="select-input-wrap">
					<select name="export_type">
						<option value="excel">Excel</option>
						<option value="pdf">PDF</option>
						<option value="html">HTML</option>
					</select>
				</div>
			</div>			
		</div>
	</div>
</form>
<!-- <link rel="stylesheet" type="text/css" href="<?= css_path('ui.multiselect.css')?>" />

<script type="text/javascript" src="<?=site_url('lib/multiselect-michael/jquery.localisation-min.js')?>"></script>
<script type="text/javascript" src="<?=site_url('lib/multiselect-michael/jquery.scrollTo-min.js')?>" ></script>
<script type="text/javascript" src="<?=site_url('lib/multiselect-michael/ui.multiselect.js')?>" ></script> -->

<script type="text/javascript">
	$('select[name="fields[]"] option').attr('selected', 'selected');
	$('select[name="employees[]"] option').attr('selected', 'selected');

	$("#multiselect-fields").multiselect().multiselectfilter({
        show:['blind',250],
        hide:['blind',250],
        selectedList: 1
    });
    $("#multiselect-employees").multiselect().multiselectfilter({
        show:['blind',250],
        hide:['blind',250],
        selectedList: 1
    });


    $('.input-radio').click(function(){
    	if($(this).val() == 1){
    		$("#multiselect-fields").val([]);

    		$(this).parents('#form-div')
    		.find('select[name="records_from"]').parent().parent()
    		.removeClass('hidden');

    		$(this).parents('#form-div')
    		.find('select[name="records_fields[]"]').parent().parent()
    		.removeClass('hidden');

    		$(this).parents('#form-div')
    		.find('select[name="fields[]"]').parent().parent()
    		.addClass('hidden');

		    $("#multiselect-recordfields").multiselect().multiselectfilter({
		        show:['blind',250],
		        hide:['blind',250],
		        selectedList: 1
		    });

    	}else{
    		$('select[name="fields[]"] option').attr('selected', 'selected');
    		$('select[name="records_from"]').val('');
			$('#multiselect-recordfields option').remove();
			$("#multiselect-recordfields").multiselect("destroy");

    		$(this).parents('#form-div')
    		.find('select[name="records_from"]').parent().parent()
    		.addClass('hidden');

    		$(this).parents('#form-div')
    		.find('select[name="records_fields[]"]').parent().parent()
    		.addClass('hidden');


    		$(this).parents('#form-div')
    		.find('select[name="fields[]"]').parent().parent()
    		.removeClass('hidden');
    	}
    });

    $('#records-from').change(function(){

			//added this as requested(#664) and since the page fields were hardcoded
	    	if($('#records-from').val() != ""){
	    		var array_tabs = new Array();
	    		switch($('#records-from').val()) {
					 case "accountabilities":    
					 	var array_tabs = new Array("Equipment", "Tag Number", "Status", "Date Issued", "Date Returned", "Cost", "Quantity", "Clearance Approver", "Remarks")
					  	break;
					 case "affiliation":
					 	var array_tabs = new Array("Name of Affiliation", "Status", "Position", "Date Resigned", "Date Joined")
					  	break;
					 case "character_reference":    
					 	var array_tabs = new Array("Name", "Address", "Company Name", "Email Address", "Telephone", "Occupation", "Years Known")
					  	break;
					 case "education":
					 	var array_tabs = new Array("Educational Attainment", "School", "Honors Received", "Graduate/Undergraduate", "Degree Obtained", "Date From", "Date To")
					  	break;
					 case "employee_trainings":    
					 	var array_tabs = new Array("Course", "Institution", "Address", "Remarks", "Date From", "Date To", "Training Status")
					  	break;
					 case "employment_history":
					 	var array_tabs = new Array("Company", "Address", "Contact No.", "Nature of Business", "Position", "Date From", "Date To", "Immediate Superior's Name", "Reason For Leaving", "Duties", "Last Salary", "Equivalent Position (FB)")
					  	break;
					 case "family":    
					 	var array_tabs = new Array("Family Member's Name", "Relationship", "Date of Birth", "Occupation", "Employer", "Family Benefit", "Degree Obtained", "Educational Attainment")
					  	break;
					 case "other_information":
					 	var array_tabs = new Array("Name", "Relation", "Occupation", "Company")
					  	break;
					 case "skill":    
					 	var array_tabs = new Array("Skill Type", "Skill Name", "Proficiency Level", "Remarks")
					  	break;
					 case "test_profile":
					 	var array_tabs = new Array("Exam Type", "Exam Title", "License Number", "Date Taken", "Given By", "Location", "Score/Rating", "Result", "Remarks")
					  	break;
					 default:
					 // code to be executed if n is different from case 1 and 2
					}
	    	}

		$('#multiselect-recordfields option').remove();

	    for(var i=0; i<array_tabs.length; i++)
	     {   
	         $('#multiselect-recordfields').append('<option>'+array_tabs[i]+'</option>');
	     }

		$('select[name="records_fields[]"] option').attr('selected', 'selected');
	    $("#multiselect-recordfields").multiselect("destroy").multiselect().multiselectfilter({
	        show:['blind',250],
	        hide:['blind',250],
	        selectedList: 1
	    }); 

    });
</script>