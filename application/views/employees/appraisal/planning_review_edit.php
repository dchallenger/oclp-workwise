<div class="spacer"></div>

<div id="form-div">                        
	<div class="">
		<div class="col-2-form">
			<div class="form-item odd">
				<div class="form-submit-btn align-left nopadding">
					<div class="icon-label-group">
				    <div class="icon-label">
				      <a href="javascript:void(0)" class="icon-16-add" onclick="add_reminder(event)">                        
				      	<span>Add Reminder</span>
				      </a>            
				    </div>
				  </div>
				</div>				
			</div>	
		</div>	
	</div>	
	<div  >
		<div id="reminder-list">
		<?php
			if (!empty($reminder) && count($reminder) > 0) {
				foreach ($reminder as $key => $value) {
				$date = ($value->date && $value->date != "0000-00-00") ? date('m/d/Y' , strtotime($value->date)) : '' ;
				$count = $key+1;
				
		?>		
			<div class="col-2-form"> 
				<h3 class="form-head">
			        <div class="align-right">
			            <span class="fh-delete"><a class="delete-detail" href="javascript:void(0)">DELETE</a></span>
			        </div>
			    </h3>
				<div class="form-item odd ">
					<div class="select-input-wrap">

						<select id="template_id" name="template[]">
							<option value="">Select...</option>
							<?php 
								if( $template_result->num_rows() > 0 ){
									foreach( $template_result->result() as $template_info ){ ?>
										<option value="<?= $template_info->template_id ?>" <?=($value->template_id == $template_info->template_id) ? "selected" : '' ;?>><?= $template_info->templatename ?></option>
							<?php 
									}
								} 
							?>
						</select>

					    <!-- <select id="planning" name="planning[]">
					        <option value="">Select…</option>
					        <option  <?=($value->appraisal_email_reminder == 1) ? "selected" : '' ;?> value="1" >Before Start Date</option>
					        <option  <?=($value->appraisal_email_reminder == 2) ? "selected" : '' ;?> value="2">Start Date</option>
					        <option  <?=($value->appraisal_email_reminder == 3) ? "selected" : '' ;?> value="3" >Before Due Date</option>
					    </select> -->
					</div>
				</div>
				<div class="form-item even ">
					<div class="text-input-wrap">
						<input type="text" class="datepicker input-text reminder_date" value="<?=$date?>" name="date[]">
						<input type="hidden" value="<?=$value->email_sent?>" name="email_sent[]">
						<input type="hidden" value="x" name="reminder[]">
					</div>
				</div>
			<div class="form-item even">
				 <label class="label-desc gray" for="attachment[]">
		            Attachment:
		        </label>
		        <div id="error-photo"></div>
			  	<?php if ($value->uploaded_file != "") 
                    { 
                        $path_info = pathinfo($value->uploaded_file);
                        if( in_array( $path_info['extension'], array('jpeg', 'jpg', 'JPEG', 'JPG', 'gif', 'GIF', 'png', 'PNG', 'bmp', 'BMP') ) )
                        {?>
                            <div class="nomargin image-wrap" id="photo-upload-container_2<?=$count;?>">
                                <img id="file-photo-<?=$count;?>" src="<?= base_url().$value->uploaded_file ?>" width="100px">
                                <div class="delete-image nomargin" field="dir_path<?=$count;?>" style="display: none;"></div>
                            </div>
                        <?php 
                        }
                        else
                        {?>
                            <div class="nomargin image-wrap" id="photo-upload-container_2<?=$count;?>">
                                <a id="file-photo-<?=$count;?>" href="<?= base_url().$value->uploaded_file ?>" width="100px"><img src="<?= base_url()?>themes/slategray/images/file-icon-md.png"></a>
                                <div class="delete-image nomargin" field="dir_path<?=$count;?>"></div>
                            </div>
                        <?php }?>
                         <div class="clear"></div>
                  <?php  }else{ ?>
                  		<div class="nomargin image-wrap" id="photo-upload-container_2<?=$count;?>"></div>
                         <div class="clear"></div> 
                  <?php	} 	?>
		       
		        <input id="dir_path<?=$count;?>" type="hidden" class="input-text" value="<?=$value->uploaded_file?>" name="attachment[]">     
		        <div><input id="attachment-photo<?=$count;?>" name="photo" type="file" rel="<?=$count;?>"/></div>
		    </div> 

		</div>

		<?php	}
			}
		?>		
		</div>
	</div>
</div>	 
<input type="hidden" class="count_attachment" value="<?=count($reminder);?>"> 