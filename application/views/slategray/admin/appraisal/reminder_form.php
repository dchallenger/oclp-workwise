
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
									<option value="<?= $template_info->template_id ?>"><?= $template_info->templatename ?></option>
						<?php 
								}
							} 
						?>
					</select>

				    <!-- <select id="planning" name="planning[]">
				        <option value="">Select…</option>
				        <option value="1">Before Start Date</option>
				        <option value="2">Start Date</option>
				        <option value="3">Before Due Date</option>
				    </select> -->
				</div>
			</div>
			<div class="form-item even ">
				<div class="text-input-wrap">
					<input type="text" class="datepicker input-text reminder_date" value="" name="date[]">
					<input type="hidden" value="" name="email_sent[]">
					<input type="hidden" value="x" name="reminder[]">
				</div>
			</div>
			<div class="form-item even">
	        <label class="label-desc gray" for="attachment[]">
	            Attachment:
	        </label>
	        <div id="error-photo"></div>
	        <div class="nomargin image-wrap" id="photo-upload-container_2<?=$count;?>"></div>
	        <div class="clear"></div>
	        <input id="dir_path<?=$count;?>" type="hidden" class="input-text" value="" name="attachment[]">                    
	        <div><input id="attachment-photo<?=$count;?>" name="photo" type="file" rel="<?=$count;?>"/></div>
	    </div> 

</div>