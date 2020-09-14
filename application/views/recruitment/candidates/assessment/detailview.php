<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); 

    ?>

<?php foreach ($exam_details['type'] as $key => $value):?>
	<div class="form-item view odd ">
	    <label class="label-desc view gray" for="exam">
	        Exam Type:
	    </label>
	    <div class="text-input-wrap"><?=$value?> </div>
	</div>
	<div class="form-item view odd ">
	    <label class="label-desc view gray" for="result">
	        Result:
	    </label>
	    <div class="text-input-wrap"><?=$exam_details['result'][$key]?> </div>
	</div>
	<div class="form-item view even">
	    <label class="label-desc view gray" for="percentile">
	        Percentiles:
	    </label>
	    <div class="text-input-wrap"> <?=$exam_details['percent'][$key]?> </div>
	</div>

<?php endforeach;?>