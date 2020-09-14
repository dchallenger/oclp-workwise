<script type="text/javascript">
	$(document).ready(function(){
		 var value = "";
		<?php
			if( isset($date) && sizeof($date) > 0) : 
				foreach($date as $param) : ?>
					// for date picker [temporary]
					$( 'input[name="<?php echo $param[0]?>-temp"]' ).datepicker({
						altField: 'input[name="<?php echo $param[0]?>"]',
						changeMonth: true,
						changeYear: true,
						showOtherMonths: true,
						showButtonPanel: true,
						showAnim: 'slideDown',
						selectOtherMonths: true,
						showOn: "both",  
                        defaultDate: null,
						buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
						buttonImageOnly: true,  
						buttonText: '',
						yearRange: 'c-90:c+10',
						beforeShow: function(input, inst) {						
							if (inst.dpDiv.hasClass('monthonly')) {
								inst.dpDiv.removeClass('monthonly');	
							}
							
							if (inst.dpDiv.hasClass('yearonly')) {
								inst.dpDiv.removeClass('yearonly');	
							}							
						}
					});                                                                                
            <? 	endforeach;?>
			<?php endif;?>
    
		<?php
			if(isset($date_from_to) && sizeof($date_from_to) > 0) : 
				foreach($date_from_to as $param) : ?>
					// for date picker [temporary]
					$( 'input[name="<?php echo $param[0]?>-temp-from"]' ).datepicker({
						altField: 'input[name="<?php echo $param[0]?>_from"]',
						changeMonth: true,
						changeYear: true,
						showOtherMonths: true,
						showButtonPanel: true,
						showAnim: 'slideDown',
						selectOtherMonths: true,
						showOn: "button",
						buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
						buttonImageOnly: true,  
						buttonText: '',
						yearRange: 'c-90:c+10',
					});
					
					// for date picker [temporary]
					$( 'input[name="<?php echo $param[0]?>-temp-to"]' ).datepicker({
						altField: 'input[name="<?php echo $param[0]?>_to"]',
						changeMonth: true,
						changeYear: true,
						showOtherMonths: true,
						showButtonPanel: true,
						showAnim: 'slideDown',
						selectOtherMonths: true,
						showOn: "button",
						buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
						buttonImageOnly: true,  
						buttonText: '',
						yearRange: 'c-90:c+10',
					});
					<?
				endforeach;
			endif;?>	
		
		<?php
			if(isset($timepicker) && sizeof($timepicker) > 0  ):
				foreach($timepicker as $param) : ?>
					$('input[name="<?php echo $param[0]?>"]').timepicker({
                        showAnim: 'slideDown',
                        selectOtherMonths: true,
                        showOn: "both",
                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                        buttonImageOnly: true,  
                        buttonText: '',                    
                        hourGrid: 4,
                        minuteGrid: 10,
                        ampm: true
                    }); <?php
				endforeach;
			endif; ?>

		<?php
			if(isset($time_start_end_picker) && sizeof($time_start_end_picker) > 0  ):
				foreach($time_start_end_picker as $param) : ?>
					$('input[name="<?php echo $param[0]?>_start"]').timepicker({
                        showAnim: 'slideDown',
                        showOn: "both",
                        timeFormat: 'hh:mm tt',
                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                        buttonImageOnly: true,  
                        buttonText: '',                    
                        hourGrid: 4,
                        minuteGrid: 10,
                        ampm: true,
                    });
    
    				$('input[name="<?php echo $param[0]?>_end"]').timepicker({
                        showAnim: 'slideDown',
                        showOn: "both",
                        timeFormat: 'hh:mm tt',
                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                        buttonImageOnly: true,  
                        buttonText: '',                    
                        hourGrid: 4,
                        minuteGrid: 10,
                        ampm: true,
                    });<?php
				endforeach;
			endif; ?>
		<?php
			if(isset($min_sec_picker) && sizeof($min_sec_picker) > 0  ):
				foreach($min_sec_picker as $param) : ?>
					$('input[name="<?php echo $param[0]?>"]').timepicker({
                        showAnim: 'slideDown',
                        showHour: false,
                        showMinute: true,
                        showSecond: true,
                        timeFormat: 'mm:ss',
                        showOn: "both",
                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                        buttonImageOnly: true,  
                        buttonText: '',                    
                        minuteGrid: 10,
                        secondGrid: 10,
                    }); <?php
				endforeach;
			endif; ?>

		<?php
			if(isset($datetime_picker) && sizeof($datetime_picker) > 0  ):
				foreach($datetime_picker as $param) : ?>
					$('input[name="<?php echo $param[0]?>"]').datetimepicker({                            
                        changeMonth: true,
                        changeYear: true,
                        showOtherMonths: true,
                        showButtonPanel: true,
                        showAnim: 'slideDown',
                        selectOtherMonths: true,
                        showOn: "both",
                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                        buttonImageOnly: true,  
                        buttonText: '',                    
                        hourGrid: 4,
                        minuteGrid: 10,
                        timeFormat: 'hh:mm tt',
                        ampm: true,
                        yearRange: 'c-90:c+10'                        
                    }); <?php
				endforeach;
			endif; ?>

		<?php
			if(isset($datetime_from_to_picker) && sizeof($datetime_from_to_picker) > 0  ):
				foreach($datetime_from_to_picker as $param) : ?>
					$('input[name="<?php echo $param[0]?>_from"]').datetimepicker({                            
                        changeMonth: true,
                        changeYear: true,
                        showOtherMonths: true,
                        showButtonPanel: true,
                        showAnim: 'slideDown',
                        selectOtherMonths: true,
                        showOn: "both",
                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                        buttonImageOnly: true,  
                        buttonText: '',                    
                        hourGrid: 4,
                        minuteGrid: 10,
                        timeFormat: 'hh:mm tt',
                        ampm: true,
                        yearRange: 'c-90:c+10'                        
                    });

                    $('input[name="<?php echo $param[0]?>_to"]').datetimepicker({                            
                        changeMonth: true,
                        changeYear: true,
                        showOtherMonths: true,
                        showButtonPanel: true,
                        showAnim: 'slideDown',
                        selectOtherMonths: true,
                        showOn: "both",
                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                        buttonImageOnly: true,  
                        buttonText: '',                    
                        hourGrid: 4,
                        minuteGrid: 10,
                        timeFormat: 'hh:mm tt',
                        ampm: true,
                        yearRange: 'c-90:c+10'                        
                    }); <?php
				endforeach;
			endif; ?>		

		<?php
			if( isset($integer) &&  sizeof($integer) > 0) : 
				foreach($integer as $param) : ?>
					 value =  $('input[name="<?php echo $param[0]?>"]').val();
					 value = addCommas(value);
					 $('input[name="<?php echo $param[0]?>"]').addClass('text-right');
					 $('input[name="<?php echo $param[0]?>"]').val(value);
					 $('input[name="<?php echo $param[0]?>"]').keyup( maskInteger );<?
				endforeach;
			endif;?>
		
		<?php
			if( isset($float) &&  sizeof($float) > 0) : 
				foreach($float as $param) : ?>
					value =  $('input[name="<?php echo $param[0]?>"]').val();
					value = addCommas(value);
					$('input[name="<?php echo $param[0]?>"]').addClass('text-right');
					$('input[name="<?php echo $param[0]?>"]').val(value);
					$('input[name="<?php echo $param[0]?>"]').keyup( maskFloat );<?
				endforeach;
			endif;?>

		<?php
			if( isset($numeric) &&  sizeof($numeric) > 0) : 
				foreach($numeric as $param) : ?>					
					$('input[name="<?php echo $param[0]?>"]').addClass('text-right');					
					$('input[name="<?php echo $param[0]?>"]').keydown( numeric_only );<?
				endforeach;
			endif;?>			

		<?php
			if(isset($password) && sizeof($password) > 0) : ?>
				$('.change-password').live('click', function (){
					$(this).css('display', 'none');
					$('.'+$(this).attr('field-div')).css('display', '');
				}); <?
			endif;?>
					
		<?php
			if( isset($single_upload) && sizeof($single_upload) > 0) : ?>
				//detect flash
				var flash_installed = detect_flash();
				if( flash_installed ){ <?php 
					foreach($single_upload as $param) : ?>
						$('#uploadify-<?php echo $param[0]?>').uploadify({
							'uploader'  : '<?php echo base_url()?>lib/uploadify214/uploadify.swf',
							'script'    : module.get_value('base_url') + "lib/uploadify214/uploadify2.php",
							'cancelImg' : '<?php echo base_url()?>lib/uploadify214/cancel.png',
							'folder'    : 'uploads/<?php echo $this->module_link?>',
							'fileExt'	: '*.jpg;*.gif;*.png;*.doc;*.docx;*.xls;*.xlsx;*.pdf;*.txt;*.dat',
							'fileDesc'  : 'Web Image Files (.JPG, .GIF, .PNG) and Text Documents and Spreadsheets (.DOC, .DOCX, .XLS, .XLSX, .PDF, .DAT)',
							'auto'      : true,
							'method'	: 'POST',
							'scriptData': {module: "<?php echo $this->module?>", fullpath: module.get_value('fullpath'), path: "uploads/<?php echo $this->module_link?>", field:"<?php echo $param[0]?>"},
							'onComplete': function(event, ID, fileObj, response, data)
							{
								var res = $.parseJSON(response)
								$('#<?php echo $param[0]?>').val(res.path);							
								$('#<?php echo $param[0]?>-upload-container').html('');
								if( res.file_type == "image" ){
									var img = '<div class="nomargin image-wrap"><img id="file-<?php echo $param[0]?>" src="<?php echo base_url()?>'+res.path+'" width="100px"><div class="image-delete nomargin multi" field="<?php echo $param[0]?>"></div></div>';
									$('#<?php echo $param[0]?>-upload-container').append(img);
								}
								else{
									var img = '<div class="nomargin image-wrap"><a id="file-<?php echo $param[0]?>" href="<?php echo base_url()?>'+res.path+'" width="100px" target="_blank"><img src="<?php echo base_url(). $this->userinfo['theme']?>/images/file-icon-md.png"></a><div class="image-delete nomargin multi" field="<?php echo $param[0]?>"></div></div>';
									$('#<?php echo $param[0]?>-upload-container').append(img);
								}		
							},
							'onError' : function (event,ID,fileObj,errorObj) {
								$('#error-<?php echo $param[0]?>').html(errorObj.type + ' Error: ' + errorObj.info);
							}
						});<?
					endforeach; ?>
				}
				else{ <?php
					foreach($single_upload as $param) : ?>
						$('form#record-form #uploadify-<?php echo $param[0]?>').addClass('hidden');
	                	$('form#record-form #error-<?php echo $param[0]?>').html('To be able to upload files, please install or enable Adobe Flash Player. <a href="http://www.adobe.com/support/flashplayer/downloads.html">Get it here.</a>')<?php
	                endforeach; ?>
				} <?php
			endif;?>			
		<?php
			if( isset($multiple_upload) && sizeof($multiple_upload) > 0) : ?>
				var flash_installed = detect_flash();
				if( flash_installed ){ <?php 
					foreach($multiple_upload as $param) : ?>
						$('#uploadify-<?php echo $param[0]?>').uploadify({
							'uploader'  : module.get_value('base_url') + 'lib/uploadify214/uploadify.swf',
							'script'    : module.get_value('base_url') + 'lib/uploadify214/uploadify2.php',
							'cancelImg' : module.get_value('base_url') + 'lib/uploadify214/cancel.png',
							'folder'    : 'uploads/<?php echo $this->module_link?>',
							'fileExt'	: '*.jpg;*.gif;*.png;*.doc;*.docx;*.xls;*.xlsx;*.pdf;*.txt',
							'fileDesc'  : 'Web Image Files (.JPG, .GIF, .PNG) and Text Documents and Spreadsheets (.DOC, .DOCX, .XLS, .XLSX, .PDF)',
							'auto'      : true,
							'scriptData': {module: "<?php echo $this->module?>", fullpath: module.get_value('fullpath'), path: "uploads/<?php echo $this->module_link?>", field:"<?php echo $param[0]?>"},
							'onComplete': function(event, ID, fileObj, response, data)
							{
								var response_data = eval( '(' + response + ')' );
								var upload_data = "module_id=<?php echo $this->module_id?>";
								upload_data = upload_data + "&field_id=<?php echo $param[2]?>";
								upload_data = upload_data + "&users_id=<?php echo $this->user->user_id?>";
								upload_data = upload_data + "&upload_path=" + response_data.path;
								
								$.ajax({
									url: module.get_value('base_url') + module.get_value('module_link') +"/file_upload",
									type:"POST",
									data: upload_data,
									dataType: "json",
									success: function(data){
										if ( data.msg != "" ) $('#message-container').html(message_growl(data.msg_type, data.msg ));
										
										if(data.upload_id != ""){
											if($('#<?php echo $param[0]?>').val() == "")
												$('#<?php echo $param[0]?>').val(data.upload_id);
											else
												$('#<?php echo $param[0]?>').val($('#<?php echo $param[0]?>').val() +','+data.upload_id);
											
											if( response_data.file_type == "image" ){
												var img = '<div class="nomargin image-wrap"><img id="file-<?php echo $param[0]?>-'+ data.upload_id +'" src="<?php echo base_url()?>'+response_data.path+'" width="100px"><div class="image-delete nomargin multi" field="<?php echo $param[0]?>" upload_id="'+ data.upload_id +'"></div></div>';
												$('#<?php echo $param[0]?>-upload-container').append(img);
											}
											else{
												var img = '<div class="nomargin image-wrap"><a id="file-<?php echo $param[0]?>-'+ data.upload_id +'" href="<?php echo base_url()?>'+response_data.path+'" width="100px" target="_blank"><img src="<?php echo base_url(). $this->userinfo['theme']?>/images/file-icon-md.png"></a><div class="image-delete nomargin multi" field="<?php echo $param[0]?>" upload_id="'+ data.upload_id +'"></div></div>';
												$('#<?php echo $param[0]?>-upload-container').append(img);
											}
										}
									}
								});
							},
							'onError'     : function (event,ID,fileObj,errorObj) {
								$('#error-<?php echo $param[0]?>').html(errorObj.type + ' Error: ' + errorObj.info);
							}
						});<?
					endforeach; ?>
				}
				else{ <?php
					foreach($multiple_upload as $param) : ?>
						$('form#record-form #uploadify-<?php echo $param[0]?>').addClass('hidden');
	                	$('form#record-form #error-<?php echo $param[0]?>').html('To be able to upload files, please install or enable Adobe Flash Player. <a href="http://www.adobe.com/support/flashplayer/downloads.html">Get it here.</a>'); <?php
	                endforeach; ?>
				} <?php
			endif;?>
		
		<?php 
			if(isset($multiselect) && sizeof($multiselect) > 0) : 
				foreach($multiselect as $param) : ?>
					$('#multiselect-<?php echo $param[0]?>').multiselect({show:['blind',250],hide:['blind',250],selectedList: 1}); <?
				endforeach;
			endif;?>
		
		<?php 
			if(isset($chosen_autocomplete) && sizeof($chosen_autocomplete) > 0) : 
				foreach($chosen_autocomplete as $param) : ?>
					$('select[id="<?php echo $param[0]?>"]').chosen({allow_single_deselect: true }); <?
				endforeach;
			endif;?>
				
		<?php
			if( isset($readonly) &&  sizeof($readonly) > 0) : 
				foreach($readonly as $fieldname) : ?>
					$('input[name="<?php echo $fieldname?>"]').attr('readonly', true);<?
				endforeach;
			endif;?>	
	});
	
	function validate_fg<?php echo $fg_id?>(){
		<?php
			if(isset($ckeditor) && sizeof($ckeditor) > 0) : 
				foreach($ckeditor as $param) : ?>
					<?php echo $param[0]?>.updateElement(); <?
				endforeach;
			endif;?>	
		
		<?php
			if( isset($multiselect) && sizeof($multiselect) > 0) : 
				foreach($multiselect as $param) : ?>
					var temp = $.map($('#multiselect-<?php echo $param[0]?>').multiselect("getChecked"),function( input ){
						return input.value;
					});
					$('input[name="<?php echo $param[0]?>"]').val(temp);
					<?
				endforeach;
			endif; ?>
		
		<?php
			if( isset($mandatory) && sizeof($mandatory) > 0) : 
				foreach($mandatory as $param) : ?>
					validate_mandatory("<?php echo $param[0]?>", "<?php echo $param[1]?>");	<?
				endforeach;
			endif;?>
		
		<?php
			if(isset($integer) && sizeof($integer) > 0) : 
				foreach($integer as $param) : ?>
					validate_integer("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>
		
		<?php
			if(isset($float) && sizeof($float) > 0) : 
				foreach($float as $param) : ?>
					validate_float("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>
		
		<?php
			if(isset($email) && sizeof($email) > 0) : 
				foreach($email as $param) : ?>
					validate_email("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>
			
		<?php
			if(isset($url) && sizeof($url) > 0) : 
				foreach($url as $param) : ?>
					validate_url("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>	
		
		<?php
			if(isset($password) && sizeof($password) > 0) : 
				foreach($password as $param) : ?>
					validate_password("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>
		
		<?php
			if(isset($ckeditor) && sizeof($ckeditor) > 0) : 
				foreach($ckeditor as $param) : ?>
					validate_ckeditor("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>	
		
		<?php
			if(isset($le) && sizeof($le) > 0) : 
				foreach($le as $param) : ?>
					validate_less_or_equal("<?php echo $param[0]?>", "<?php echo $param[1]?>", "<?php echo $param[2]?>"); <?
				endforeach;
			endif;?>
		
		<?php
			if( isset($lt) && sizeof($lt) > 0) : 
				foreach($lt as $param) : ?>
					validate_less_than("<?php echo $param[0]?>", "<?php echo $param[1]?>", "<?php echo $param[2]?>"); <?
				endforeach;
			endif;?>
		
		<?php
			if(isset($ge) && sizeof($ge) > 0) : 
				foreach($ge as $param) : ?>
					validate_greater_or_equal("<?php echo $param[0]?>", "<?php echo $param[1]?>", "<?php echo $param[2]?>"); <?
				endforeach;
			endif;?>
		
		<?php
			if(isset($gt) && sizeof($gt) > 0) : 
				foreach($gt as $param) : ?>
					validate_greater_than("<?php echo $param[0]?>", "<?php echo $param[1]?>", "<?php echo $param[2]?>"); <?
				endforeach;
			endif;?>
		
		//errors
		if(error.length > 0){
			var error_str = "Please correct the following errors:<br/><br/>";
			for(var i in error){
				if(i == 0) $('#'+error[i][0]).focus(); //set focus on the first error
				error_str = error_str + (parseFloat(i)+1) +'. '+error[i][1]+" - "+error[i][2]+"<br/>";
			}
			$('#message-container').html(message_growl('error', error_str));
			
			//reset errors
			error = new Array();
			error_ctr = 0
			return false;
		}
		
		//no error occurred
		return true;
	}
</script>