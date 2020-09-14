<?php if(isset($scripts)) echo is_array($scripts) ? implode("\r\n", $scripts) : $scripts;?>
<script type="text/javascript">
	var quickedit_for = "<?php echo $module?>";	
	function <?php echo $this->module;?>_quick_ajax_save( e ){
        quickedit_submit_to = module.get_value('base_url') + "<?php echo $module_link?>/ajax_save";
		if( validate_quickform() ){
			$('#'+e+'-quick-edit-form').find('.chzn-done').each(function (index, elem) {
				if (elem.multiple) {
					if ($(elem).attr('name') != $(elem).attr('id') + '[]') {
						$(elem).attr('name', $(elem).attr('name') + '[]');
					}
					
					var values = new Array();
					for(var i=0; i< elem.options.length; i++) {
						if(elem.options[i].selected == true) {
							values[values.length] = elem.options[i].value;
						}
					}
					$(elem).val(values);
				}
			});

			var data = $('#'+e+'-quick-edit-form').serialize();
			var saveUrl = quickedit_submit_to;
                        
			$.ajax({
				url: saveUrl,
				type:"POST",
				data: data+"&quick_edit_flag=true",
				dataType: "json",
				beforeSend: function(){
					$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Saving, please wait...</div>' });
				},
				success: function(data){ <?php 
					if( isset( $_POST['quick_add'] ) && $_POST['quick_add'] ) :                                                                                                
					// Extract column names if from multiple.
						// Prior to this no text is displayed to the input if multiple columns are used.
						$columns = explode(',', $_POST['column_value_from']); ?>
						var column_value_from = ''; <?php
						foreach ($columns as $column):?>
						 column_value_from += $('input[name="<?php echo $column?>"]').val() + ' '; <?
						endforeach;
					endif; ?>

					//implemented in module js
					if(typeof quickedit_boxy_callback == typeof(Function)) quickedit_boxy_callback( e );
          			<?php if( $this->input->post('page_refresh') ) :?>
						if( data.page_refresh && data.msg_type != 'error') page_refresh();
					<?php endif;?> 
					
					if (data.msg_type == 'success') {
						Boxy.get('#'+e+'-quick-edit-form').hide().unload();
						$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } }); <?php
						if( isset( $_POST['quick_add'] ) && $_POST['quick_add'] ) :?>
							$('input[name="<?php echo $_POST['field_to_fill']?>"]').val(data.record_id);
							$('input[name="<?php echo $_POST['field_to_fill']?>"]').trigger('change');
							$('input[name="<?php echo $_POST['field_to_fill']?>-name"]').val(column_value_from);
							related_module_boxy[<?php echo $fmlinkctr?>].hide().unload();<?php
						endif;?>
					} else {
						$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
					}
				}
			});
		}
	}
</script>
<!-- PLACE YOUR MAIN CONTENT HERE -->
<form class="style2 edit-view" id="<?php echo $module;?>-quick-edit-form" name="quick-edit-form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="record_id" id="record_id" value="<?php echo $this->input->post('record_id')?>" />
   	<?php if($this->input->post('module_id')):?> 
    	<input type="hidden" name="module_id" id="module_id" value="<?php echo $this->input->post('module_id')?>" />
	<?php endif;?>
    <div id="form-div">
        <?php
            if(isset($fieldgroups) && sizeof($fieldgroups) > 0) :
                // for javascript validation
				$mandatory = array();
				$integer = array();
				$float = array();
				$email = array();
				$url = array();
				$password = array();
				$ckeditor = array();
				$single_upload = array();
				$multiple_upload = array();
				$le = array();
				$lt = array();
				$ge = array();
				$gt = array();
				$multiselect = array();
				$date = array();
				$chosen_autocomplete = array();

                foreach($fieldgroups as $fieldgroup) :  ?>
                    <div fg_id="<?php echo $fieldgroup['fieldgroup_id'] ?>" id="fg-<?php echo $fieldgroup['fieldgroup_id'] ?>" class="<?php echo $show_wizard_control ? 'wizard-type-form hidden' : '' ?>">
                    <h3 class="form-head"><?= $fieldgroup['fieldgroup_label'] ?><?php if (!$show_wizard_control) : ?><a href="javascript:void(0)" class="align-right other-link noborder" onclick="toggleFieldGroupVisibility( $( this ) );" style="font-size: 12px;line-height: 18px;">Hide</a><?php endif; ?></h3>
                    <?php if ($fieldgroup['description'] != ''):?>
                        <p><small><?=$fieldgroup['description']?></small></p>
                        <div class="spacer"></div>
                    <?php endif;?>
                    <div class="<?=!empty($fieldgroup['layout']) ? 'col-1-form' : 'col-2-form'?>">
		                <?php
		                   	if ($fieldgroup['edit_customview'] != "" && $fieldgroup['edit_customview_position'] == 1)
		                        $this->load->view($this->userinfo['rtheme'] . '/' . $fieldgroup['edit_customview']);
		                                    
		                    foreach($fieldgroup['fields'] as $field) :
		                        //set js validation params
		                        $datatypes = explode('~', $field['datatype']);
		                        $is_mandatory = false;
								$is_readonly = in_array( 'R', $datatypes )? true : false;
		                        if( $is_readonly ) $readonly[] = $field['fieldname'];
		                        foreach($datatypes as $datatype)
		                        {
		                            if($datatype == "M")
		                            {
		                                $mandatory[] = array($field['fieldname'], $field['fieldlabel']);
		                                $is_mandatory = true;
		                            }
		                            if($datatype == "I" && !$is_readonly) $integer[] = array($field['fieldname'], $field['fieldlabel']);
									if($datatype == "F" && !$is_readonly) $float[] = array($field['fieldname'], $field['fieldlabel']);
									if($datatype == "E") $email[] = array($field['fieldname'], $field['fieldlabel']);
									if($datatype == "U") $url[] = array($field['fieldname'], $field['fieldlabel']);
									if($datatype == "P") $password[] = array($field['fieldname'], $field['fieldlabel']);
									if( preg_match("/LE/", $datatype) > 0 ) $le[] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
									if( preg_match("/LT/", $datatype) > 0 ) $lt[] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
									if( preg_match("/GE/", $datatype) > 0 ) $ge[] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
									if( preg_match("/GT/", $datatype) > 0 ) $gt[] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
		                        }
		                        $this->uitype_edit->showFieldInput($field , $is_mandatory);
								if($field['uitype_id'] == 5 && !$is_readonly) $date[] = array($field['fieldname'], $field['fieldlabel']);
								if($field['uitype_id'] == 24 && !$is_readonly)  $date_from_to[] = array($field['fieldname'], $field['fieldlabel']);
		            			if($field['uitype_id'] == 26 && !$is_readonly) $time_start_end[] = array($field['fieldname'], $field['fieldlabel']);
								if($field['uitype_id'] == 16) $ckeditor[] = array($field['fieldname'], $field['fieldlabel']);
								if($field['uitype_id'] == 11) $single_upload[] = array($field['fieldname'], $field['fieldlabel']);
								if($field['uitype_id'] == 20) $multiple_upload[] = array($field['fieldname'], $field['fieldlabel'], $field['field_id']);
								if($field['uitype_id'] == 21) $multiselect[] = array($field['fieldname'], $field['fieldlabel']);
								if($field['uitype_id'] == 39 || $field['uitype_id'] == 28) $chosen_autocomplete[] = array($field['fieldname'], $field['fieldlabel']);
		                    endforeach;   
		                    if ($fieldgroup['edit_customview'] != "" && $fieldgroup['edit_customview_position'] == 3)
            					$this->load->view($this->userinfo['rtheme'] . '/' . $fieldgroup['edit_customview']);
		                ?>                
                    </div>
                    <div class="spacer"></div></div> <?php;
            	endforeach;
                //load additional js base on field and validation
				if( sizeof($ckeditor) > 0 ) echo CKEditorScript();
				if( sizeof($multiselect) > 0 ) echo multiselect_script();
				if( sizeof($single_upload) > 0 || sizeof($multiple_upload) > 0 ) echo uploadify_script();
				if ( sizeof($chosen_autocomplete) > 0) echo chosen_script();

               	//create js validation
               	//$this->load->view($this->userinfo['rtheme'].'/template/js-form-validate-quickedit', $js);

            endif;

            if( sizeof($views) > 0 ) :
                foreach($views as $view) :
                    $this->load->view($this->userinfo['rtheme'].'/'.$view);
                endforeach;
            endif; ?>        
        <div class="clear"></div>
    </div>

    <div class="form-submit-btn">
        
        <div class="icon-label-group">
            <?php if($this->user_access[$this->module_id]['add'] == 1 && $this->input->post('record_id') == '-1'): ?>
	            <div class="icon-label">
	                <a rel="record-save-<?php echo $this->module?>" class="icon-16-disk" href="javascript:void(0);" onclick="<?php echo $this->module;?>_quick_ajax_save('<?php echo $this->module?>');">
	                    <span>Save</span>
	                </a>
	            </div>
            <?php endif; ?>
        	<?php if($this->user_access[$this->module_id]['cancel'] == 1 && $this->input->post('record_id') != '-1'):
		      $show_or = true;?>
		          <div class="icon-label">
		              <a class="icon-16-cancel" href="javascript:void(0);" onclick="change_obt_status(<?php echo $this->input->post('record_id')?>, 5)">
		                  <span>Cancel</span>
		              </a>            
		          </div>
		    <?php endif; ?>
        </div>
        <div class="or-cancel">
            <span class="or">or</span>
            <a class="cancel" href="javascript:void(0)" onclick="Boxy.get(this).hide().unload()">Close</a>
        </div>
    </div>
</form>
 <div class="clear"></div>
<!-- END MAIN CONTENT -->

<script type="text/javascript">        
    function init_quickedit_datepick() {
		<?	if(sizeof($date) > 0) :?>
       	<?php foreach($date as $param) : ?>
                    // for date picker [temporary]
                    $( 'input[name="<?php echo $param[0]?>-temp"]' ).not('.hasDatePicker').datepicker({
                            altField: 'input[name="<?php echo $param[0]?>"]',
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
                            yearRange: 'c-90:c+10'
                    });<?
            endforeach;?>
    	<?php endif;?>   
			<?php
			if(isset($date_from_to) && sizeof($date_from_to) > 0) : 
				foreach($date_from_to as $param) : ?>
					// for date picker [temporary]
					$( 'input[name="<?php echo $param[0]?>-temp-from"]' ).not('.hasDatePicker').datepicker({
						altField: 'input[name="<?php echo $param[0]?>_from"]',
						changeMonth: true,
						changeYear: true,
						showOtherMonths: true,
						showButtonPanel: true,
						showAnim: 'slideDown',
						selectOtherMonths: true,
						showOn: "button",
						yearRange: 'c-90:c+10',
						buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
						buttonImageOnly: true,  
						buttonText: '',
						onClose: function(dateText) {							
							if (dateText != '') {
								if (typeof(<?php echo $param[0]?>_from_close) == typeof(Function)) {
									<?php echo $param[0]?>_from_close(dateText);
								}
							}
						}
					});
					
					// for date picker [temporary]
					$( 'input[name="<?php echo $param[0]?>-temp-to"]' ).not('.hasDatePicker').datepicker({
						altField: 'input[name="<?php echo $param[0]?>_to"]',
						changeMonth: true,
						changeYear: true,
						showOtherMonths: true,
						showButtonPanel: true,
						showAnim: 'slideDown',
						selectOtherMonths: true,
						showOn: "button",
						yearRange: 'c-90:c+10',
						buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
						buttonImageOnly: true,  
						buttonText: '',
						onClose: function(dateText) {
							if (dateText != '') {
								if (typeof(<?php echo $param[0]?>_to_close) == typeof(Function)) {
									<?php echo $param[0]?>_to_close(dateText);
								}
							}
						}							
					});
					<?
				endforeach;
			endif;?>	                            
    } 

    function BindLoadEvents() {
	        if (typeof(init_quickedit_datepick) == typeof(Function)) {
	            setTimeout(
	            	function () {init_quickedit_datepick()},
	            	1000
	            	);
	        }
		<?	if(sizeof($integer) > 0) :
				foreach($integer as $param) : ?>
					 var value =  $('input[name="<?php echo $param[0]?>"]').val();
					 value = addCommas(value);
					 $('input[name="<?php echo $param[0]?>"]').val(value);
					 $('input[name="<?php echo $param[0]?>"]').keyup( maskInteger );<?
				endforeach;
			endif;?>

		<?	if(sizeof($float) > 0) :
				foreach($float as $param) : ?>
					var value =  $('input[name="<?php echo $param[0]?>"]').val();
					value = addCommas(value);
					$('input[name="<?php echo $param[0]?>"]').val(value);
					$('input[name="<?php echo $param[0]?>"]').keyup( maskFloat );<?
				endforeach;
			endif;?>

		<?	if(sizeof($password) > 0) : ?>
				$('.change-password').live('click', function (){
					$(this).css('display', 'none');
					$('.'+$(this).attr('field-div')).css('display', '');
				}); <?
			endif;?>

		<?	if(sizeof($ckeditor) > 0) :
				foreach($ckeditor as $param) : ?>
					$('#<?php echo $param[0]?>').ckeditor();<?
				endforeach;
			endif;?>

		<?	if(sizeof($single_upload) > 0) :
				foreach($single_upload as $param) : ?>
					$('#uploadify-<?php echo $param[0]?>').uploadify({
						'uploader'  : '<?php echo base_url()?>lib/uploadify214/uploadify.swf',
						'script'    : module.get_value('base_url') + "lib/uploadify214/uploadify.php",
						'cancelImg' : '<?php echo base_url()?>lib/uploadify214/cancel.png',
						'folder'    : 'media/<?php echo $this->module?>',
						'fileExt'	: '*.jpg;*.gif;*.png',
						'fileDesc'  : 'Web Image Files (.JPG, .GIF, .PNG)',
						'auto'      : true,
						'method'	: 'POST',
						'scriptData': {module: "<?php echo $this->module?>", fullpath: module.get_value('fullpath'), path: "media/<?php echo $this->module?>", field:"<?php echo $param[0]?>"},
						'onComplete': function(event, ID, fileObj, response, data)
						{
							$('#<?php echo $param[0]?>').val(response);
							$('#file-<?php echo $param[0]?>').attr('src', module.get_value('base_url') + response);
						},
						'onError'     : function (event,ID,fileObj,errorObj) {
							$('#error-<?php echo $param[0]?>').html(errorObj.type + ' Error: ' + errorObj.info);
						}
					});<?
				endforeach;
			endif;?>

		<?	if(sizeof($multiple_upload) > 0) :
				foreach($multiple_upload as $param) : ?>
					$('#uploadify-<?php echo $param[0]?>').uploadify({
						'uploader'  : module.get_value('base_url') + 'lib/uploadify214/uploadify.swf',
						'script'    : module.get_value('base_url') + 'lib/uploadify214/uploadify.php',
						'cancelImg' : module.get_value('base_url') + 'lib/uploadify214/cancel.png',
						'folder'    : 'media/<?php echo $this->module?>',
						'fileExt'	: '*.jpg;*.gif;*.png',
						'fileDesc'    : 'Web Image Files (.JPG, .GIF, .PNG)',
						'auto'      : true,
						'scriptData': {module: "<?php echo $this->module?>", fullpath: module.get_value('fullpath'), path: "media/<?php echo $this->module_link?>", field:"<?php echo $param[0]?>"},
						'onComplete': function(event, ID, fileObj, response, data)
						{
							var upload_data = "module_id=<?php echo $this->module_id?>";
							upload_data = upload_data + "&field_id=<?php echo $param[2]?>";
							upload_data = upload_data + "&users_id=<?php echo $this->user->user_id?>";
							upload_data = upload_data + "&upload_path=" + response;
							$.ajax({
								url: module.get_value('base_url') + module_link +"/file_upload",
								type:"POST",
								data: upload_data,
								dataType: "json",
								success: function(data){
									if ( data.msg != "" ) {
										$('#message-container').html(message_growl(data.msg_type, data.msg ));
									}

									if(data.upload_id != "")
									{
										if($('#<?php echo $param[0]?>').val() == "")
										{
											$('#<?php echo $param[0]?>').val(data.upload_id);
										}
										else{
											$('#<?php echo $param[0]?>').val($('#<?php echo $param[0]?>').val() +','+data.upload_id);
										}

										var img = '<div class="nomargin image-wrap"><img id="file-<?php echo $param[0]?>-'+ data.upload_id +'" src="<?php echo base_url()?>'+response+'" width="100px"><div class="image-delete nomargin multi" field="<?php echo $param[0]?>" upload_id="'+ data.upload_id +'"></div></div>';
										$('#<?php echo $param[0]?>-upload-container').append(img);
									}
								}
							});
						},
						'onError'     : function (event,ID,fileObj,errorObj) {
							$('#error-<?php echo $param[0]?>').html(errorObj.type + ' Error: ' + errorObj.info);
						}
					});<?
				endforeach;
			endif;?>

		<?php 
			if(isset($chosen_autocomplete) && sizeof($chosen_autocomplete) > 0) : 
				foreach($chosen_autocomplete as $param) : ?>
					setTimeout(
						function () {
							$('#<?php echo $module;?>-quick-edit-form #<?php echo $param[0]?>').chosen()
						}	
						, 100)
					; <?
				endforeach;
			endif;?>

		<? 	if(sizeof($multiselect) > 0) :
				foreach($multiselect as $param) : ?>
					$('#multiselect-<?php echo $param[0]?>').multiselect().multiselectfilter({show:['blind',250],hide:['blind',250],selectedList: 1}); <?
				endforeach;
			endif;?>
		
		<?php
			if( isset($readonly) &&  sizeof($readonly) > 0) : 
				foreach($readonly as $fieldname) : ?>
					$('input[name="<?php echo $fieldname?>"]').attr('readonly', true);<?
				endforeach;
			endif;?>		
	}

	function validate_quickform()
	{
		<?	if(sizeof($multiselect) > 0) :
				foreach($multiselect as $param) : ?>
					var temp = $.map($('#<?php echo $this->module?>-quick-edit-form #multiselect-<?php echo $param[0]?>').multiselect("getChecked"),function( input ){
						return input.value;
					});
					$('input[name="<?php echo $param[0]?>"]').val(temp);
					<?
				endforeach;
			endif; ?>

		<?	if(sizeof($mandatory) > 0) :
				foreach($mandatory as $param) : ?>
					validate_mandatory("<?php echo $param[0]?>", "<?php echo $param[1]?>");	<?
				endforeach;
			endif;?>

		<?	if(sizeof($integer) > 0) :
				foreach($integer as $param) : ?>
					validate_integer("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>

		<?	if(sizeof($float) > 0) :
				foreach($float as $param) : ?>
					validate_float("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>

		<?	if(sizeof($email) > 0) :
				foreach($email as $param) : ?>
					validate_email("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>

		<?	if(sizeof($url) > 0) :
				foreach($url as $param) : ?>
					validate_url("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>

		<?	if(sizeof($password) > 0) :
				foreach($password as $param) : ?>
					validate_password("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>

		<?	if(sizeof($ckeditor) > 0) :
				foreach($ckeditor as $param) : ?>
					validate_ckeditor("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>

		<?	if(sizeof($le) > 0) :
				foreach($le as $param) : ?>
					validate_less_or_equal("<?php echo $param[0]?>", "<?php echo $param[1]?>", "<?php echo $param[2]?>"); <?
				endforeach;
			endif;?>

		<?	if(sizeof($lt) > 0) :
				foreach($lt as $param) : ?>
					validate_less_than("<?php echo $param[0]?>", "<?php echo $param[1]?>", "<?php echo $param[2]?>"); <?
				endforeach;
			endif;?>

		<?	if(sizeof($ge) > 0) :
				foreach($ge as $param) : ?>
					validate_greater_or_equal("<?php echo $param[0]?>", "<?php echo $param[1]?>", "<?php echo $param[2]?>"); <?
				endforeach;
			endif;?>

		<?	if(sizeof($gt) > 0) :
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
			//alert(error_str);

			//reset errors
			error = new Array();
			error_ctr = 0
			return false;
		}

		//no error occurred
		return true;
	} 
</script>