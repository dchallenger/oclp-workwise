<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	$interview = json_decode($appraisal->interview_details, true);

	if ($interview && count($interview['interviewer']) > 0):
		foreach ($interview['type'] as $key => $value):
?>
	 <div class="form-item odd view">
        <label class="label-desc view gray" for="interview_type">
            Interview Type:
        </label>
        <div class="text-input-wrap"> <?=$value?>  </div>
    </div>
    <div class="form-item even view">
        <label class="label-desc view gray" for="interviewer">
            Interviewer:
        </label>
        <div class="text-input-wrap">
           	<?php $interviewer = $this->system->get_employee($interview['interviewer'][$key]);
           		echo $interviewer['firstname'] .' '. $interviewer['lastname'];
           	  ?>
        </div>
    </div>
	<div class="form-item view odd ">
	    <label class="label-desc view gray" for="result">
	        Result:
	    </label>
	    <div class="text-input-wrap"><?=($interview['result'][$key] == '1') ? 'Passed' : 'Failed' ;?> </div>
	</div>

	<div class="form-item even view">
			<label class="label-desc view gray" for="attachment[]">
	            Attachment:
	        </label>
         <?php

                $full_file = $interview['attachment'][$key];
                $file = explode("/",$interview['attachment'][$key]);
                $data = $file[3];
                $filepath = "uploads/recruitment/candidate_result/";
                $filename = base_url() . $value->uploaded_file;
            ?>
         <div class="text-input-wrap"><a href="<?= site_url() ?><?= $filepath ?><?= $data ?>"><?= $data ?></a></div> 
       
    </div> 
    <div class="form-item view even ">
	    <label class="label-desc view gray" for="recommendation">
	        Recommendation:
	    </label>
	    <div class="text-input-wrap">
	    	<?php foreach ($recommendation as $recommend) {
	    		if ($recommend->recommendation_id == $interview['recommendation'][$key]) {
	    			echo $recommend->recommendation;
	    		}
	    	}?>
	    </div>
	</div>

    <div class="clear"></div> 

<?php endforeach; 
	endif;?>