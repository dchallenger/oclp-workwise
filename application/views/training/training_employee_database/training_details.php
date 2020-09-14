<?php 
	if( $employee_training_total > 0 ){
		foreach( $employee_training as $employee_training_info ){
?>

<div class="col-2-form view">     
    <div class="form-item view odd ">
        <label class="label-desc view gray" for="training_course_id">Training Course:</label>
        <div class="text-input-wrap">
            <?= $employee_training_info['training_subject'] ?>                  
        </div>		
    </div>	

    <div class="form-item view even">
        <label class="label-desc view gray" for="start_date">Start Date:</label>
        <div class="text-input-wrap">
            <?= $employee_training_info['start_date'] ?>                  
        </div>      
    </div>  

    <div class="form-item view odd">
        <label class="label-desc view gray" for="provider">Training Provider:</label>
        <div class="text-input-wrap">
            <?= $employee_training_info['training_provider'] ?>
        </div>      
    </div>  

    <div class="form-item view even">
        <label class="label-desc view gray" for="end_date">End Date:</label>
        <div class="text-input-wrap">
            <?= $employee_training_info['end_date'] ?>                  
        </div>      
    </div>  
    
    <div class="form-item view odd">
        <label class="label-desc view gray" for="provider">Training Type:</label>
        <div class="text-input-wrap">
            <?= $employee_training_info['training_type'] ?>
        </div>      
    </div>  

    <div class="form-item view even">
        <label class="label-desc view gray" for="venue">Venue:</label>
        <div class="text-input-wrap">
            <?= $employee_training_info['venue'] ?>                  
        </div>		
    </div>	
 
</div>

<?php
		}
	}
	else{
	?>

	<div style="text-align:center;" >
		No Record Found
	</div>

	<?php
	}
?>