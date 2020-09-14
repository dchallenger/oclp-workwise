<div  style="width:85%; min-width:800px;">
    <div>
        <h2><?php echo $calendar_info->training_subject; ?></h1>
    </div>
    <div>
        <p>Training Date:&nbsp; <?php echo $start_date; ?></p>
    </div>
    <hr />
    <div>
        <h3 class="form-head">Training Calendar</h3>
        <div class="col-2-form view">     
            <div class="form-item view odd ">
                <label class="label-desc view gray" for="training_provider">Training Course:</label>
                <div class="text-input-wrap">
                    <?= $calendar_info->training_subject; ?>                  
                </div>		
            </div>	
            <div class="form-item view even ">
                <label class="label-desc view gray" for="training_provider_code">Training Topic:</label>
                <div class="text-input-wrap">
                    <?= $calendar_info->topic; ?>                  
                </div>		
            </div>	
            <div class="form-item view odd ">
                <label class="label-desc view gray" for="training_provider">Training Provider:</label>
                <div class="text-input-wrap">
                   <?= $calendar_info->training_provider; ?>                  
                </div>      
            </div>  
            <!--
            <div class="form-item view even ">
                <label class="label-desc view gray" for="training_provider_code">Publish Date:</label>
                <div class="text-input-wrap">
                    <?= date('m/d/Y',strtotime($calendar_info->publish_date)); ?>                     
                </div>      
            </div>  
        -->
            <div class="form-item view odd ">
                <label class="label-desc view gray" for="training_provider">Training Venue:</label>
                <div class="text-input-wrap">
                    <?= $calendar_info->venue; ?>                    
                </div>      
            </div>  
            <!--
            <div class="form-item view odd ">
                <label class="label-desc view gray" for="training_provider">Registration Date:</label>
                <div class="text-input-wrap">
                    <?= date('m/d/Y',strtotime($calendar_info->registration_date)); ?>                   
                </div>      
            </div>  
            <div class="form-item view odd ">
                <label class="label-desc view gray" for="training_provider">Last Registration Date:</label>
                <div class="text-input-wrap">
                    <?= date('m/d/Y',strtotime($calendar_info->last_registration_date)); ?>                         
                </div>      
            </div>  
        -->
        </div> 
    </div>   

    <div>
        <h3 class="form-head">Training Session</h3>
        <div class="col-2-form view">  
    <?php 
        if( $session_result->num_rows() > 0 ){ 
            foreach( $session_result->result() as $session_info ){
    ?>


        <div>
            <div class="form-item view odd ">
                <label class="label-desc view gray" for="training_provider">Session No.:</label>
                <div class="text-input-wrap">
                    <?= $session_info->session_no ?>                    
                </div>      
            </div>  
            <div class="form-item view even ">
                <label class="label-desc view gray" for="training_provider_code">Training Date:</label>
                <div class="text-input-wrap">
                    <?= date('F d, Y',strtotime($session_info->session_date)); ?>                     
                </div>      
            </div>  
            <div class="form-item view even ">
                <label class="label-desc view gray" for="training_provider_code">Session time:</label>
                <div class="text-input-wrap">
                    <?= date('h:i a',strtotime($session_info->sessiontime_from)) ?> - <?= date('h:i a',strtotime($session_info->sessiontime_to)) ?>                   
                </div>      
            </div>  
        </div>
        <div style="clear:both;"></div>
            <br />
     

    <?php 
            }
        } 
        
    ?> 
    	</div> 
    </div>


    <?php 

        if( $participant_result != "" && ( $participant_result->num_rows() > 0 && $subordinate > 0 ) ){ 

      ?>

        <div>
		    <h3 class="form-head">Training Participants</h3>
		     
		    <div class="col-2-form view">  
      <?php


            foreach( $participant_result->result() as $participant_info ){
    ?>
  			<div>
			    <div class="form-item view odd ">
	                <label class="label-desc view gray" for="training_provider">Participant Name</label>
	                <div class="text-input-wrap">
	                    <?= $participant_info->firstname.' '.$participant_info->lastname ?>                    
	                </div>      
	            </div>  
	            <div class="form-item view even ">
	                <label class="label-desc view gray" for="training_provider_code">Status:</label>
	                <div class="text-input-wrap">
	                    <?= $participant_info->participant_status ?>                     
	                </div>      
	            </div>  

	            <div class="form-item view even ">
	                <label class="label-desc view gray" for="training_provider_code">No Show:</label>
	                <div class="text-input-wrap">
	                	<?php if( $participant_info->no_show == 1 ){ ?>
	                		Yes
	                	<?php }else{ ?>
	                		No
	                	<?php } ?>
	                </div>      
	            </div>     

            </div>
            <div style="clear:both;"></div>
            <br />

    <?php 
            }

    ?>

	        </div> 
	    </div>  


    <?php

        } 
        
    ?> 

</div>