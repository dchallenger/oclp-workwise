<div class="spacer"></div>

<div id="form-div">                        
	<div  >
		<div id="reminder-list">
		<?php
			if (!empty($reminder) && count($reminder) > 0) {
				foreach ($reminder as $key => $value) {
				$date = ($value->date && $value->date != "") ? date('m/d/Y' , strtotime($value->date)) : '' ;
				switch ($value->appraisal_email_reminder) {
					case '1':
						$email_date = "Before Start Date";
						break;
					case '2':
						$email_date = "Start Date";
						break;
					case '3':
						$email_date = "Before Due";
						break;
					
				}

				$tenmplatename = "";

				if( $template_result->num_rows() > 0 ){
					foreach( $template_result->result() as $template_info ){ 

						if( $value->template_id == $template_info->template_id ){
							$templatename = $template_info->templatename;
						}						
			
					}
				} 
							
				
		?>		
			<div class="col-2-form"> 
				<!-- <div class="form-item odd view ">
				<label class="label-desc gray" for="attachment[]">
			            Reminder:
			        </label>
					<div class="select-input-wrap">

					    <?=$email_date?>
					</div>
				</div> -->
				<div class="form-item odd view ">
				<label class="label-desc gray" for="attachment[]">
			            Template:
			        </label>
					<div class="select-input-wrap">
					    <?=$templatename?>
					</div>
				</div>
				<div class="form-item even view">
					 <label class="label-desc gray" for="">
			            Date:
			        </label>
					<div class="text-input-wrap"><?=$date?></div>
				</div>
				<div class="form-item even view">
					<label class="label-desc gray" for="attachment[]">
			            Attachment:
			        </label>
		         <?php

                        $full_file = $value->uploaded_file;
                        $file = explode("/",$value->uploaded_file);
                        $data = $file[3];
                        $filepath = "uploads/appraisal/appraisal_planning_period/";
                        $filename = base_url() . $value->uploaded_file;
                    ?>
		         <div class="text-input-wrap"><a href="<?= site_url() ?><?= $filepath ?><?= $data ?>"><?= $data ?></a></div> 
		       
		    </div> 

		</div>

		<?php	}
			}
		?>	
		</div>
		<div class="clear"></div>
	</div>
</div>	 
