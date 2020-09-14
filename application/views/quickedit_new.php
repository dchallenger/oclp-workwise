<?php if(isset($scripts)) echo is_array($scripts) ? implode("\r\n", $scripts) : $scripts;?>
<script type="text/javascript">	
	var quickedit_for = "<?php echo $module?>";
	var quickedit_submit_to = module.get_value('base_url') + "<?php echo $module_link?>/ajax_save";
	function quick_ajax_save( elem ){
		if( validate_quickform() ){
			var data = $('#<?php echo $module;?>-quick-edit-form').serialize();
			var saveUrl = quickedit_submit_to;
			$.ajax({
				url: saveUrl,
				type:"POST",
				data: data+"&quick_edit_flag=true",
				dataType: "json",
				beforeSend: function(){
					$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Saving, please wait...</div>' });        		
				},
				success: function(data){
					<?php
						if( isset( $_POST['quick_add'] ) && $_POST['quick_add'] ) :?>
							var column_value_from = $('input[name="<?php echo $_POST['column_value_from']?>"]').val();<?
						endif;
					?>
					
					//implemented in module js
					if(typeof window.quickedit_boxy_callback == "function") quickedit_boxy_callback( elem );
					
					quickedit_boxy.hide().unload();					
					$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
					<?php
						if( isset( $_POST['quick_add'] ) && $_POST['quick_add'] ) :?>
							$('input[name="<?php echo $_POST['field_to_fill']?>"]').val(data.record_id);
							$('input[name="<?php echo $_POST['field_to_fill']?>"]').trigger('change');
							$('input[name="<?php echo $_POST['field_to_fill']?>-name"]').val(column_value_from);
							related_module_boxy[<?php echo $fmlinkctr?>].unload();<?
						endif;
					?>
				}
			}); 
		}
	}
</script>
<!-- PLACE YOUR MAIN CONTENT HERE -->
<form class="style2 edit-view" id="<?php echo $module;?>-quick-edit-form" name="quick-edit-form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="record_id" id="record_id" value="<?php echo $this->input->post('record_id')?>" />
    <div id="form-div">
        <?php
					if(isset($fieldgroups) && sizeof($fieldgroups) > 0) :
							$load_jqgrid_in_boxy = false;
							$load_ckeditor = false;
							$load_multiselect = false;
							$load_uploadify = false;
							$js = array();
							foreach($fieldgroups as $fieldgroup) : ?>
								<div fg_id="<?php echo $fieldgroup['fieldgroup_id']?>" id="fg-<?php echo $fieldgroup['fieldgroup_id']?>" class="<?php echo $show_wizard_control ? 'wizard-type-form hidden' : ''?>">
									<h3 class="form-head"><?=$fieldgroup['fieldgroup_label']?><?php if( !$show_wizard_control ) :?><a href="javascript:void(0)" class="align-right other-link noborder" onclick="toggleFieldGroupVisibility( $( this ) );" style="font-size: 12px;line-height: 18px;">Hide</a><?php endif;?></h3>
									<div class="<?=!empty($fieldgroup['layout']) ? 'col-1-form' : 'col-2-form'?>" >
									<?php
									if( $fieldgroup['edit_customview'] != "" && $fieldgroup['edit_customview_position'] == 1)  $this->load->view( $this->userinfo['rtheme'].'/'.$fieldgroup['edit_customview'] );
									if( isset($fieldgroup['fields']) && sizeof($fieldgroup['fields']) > 0 ) :
											foreach($fieldgroup['fields'] as $field) :
													//set js validation params
													$datatypes = explode('~', $field['datatype']);
													$is_mandatory = false;
													foreach($datatypes as $datatype)
													{
															if($datatype == "M"){
																	$js['mandatory'][] = array($field['fieldname'], $field['fieldlabel']);
																	$is_mandatory = true;
															}
															if($datatype == "I") $js['integer'][] = array($field['fieldname'], $field['fieldlabel']);
															if($datatype == "F") $js['float'][] = array($field['fieldname'], $field['fieldlabel']);
															if($datatype == "E") $js['email'][] = array($field['fieldname'], $field['fieldlabel']);
															if($datatype == "U") $js['url'][] = array($field['fieldname'], $field['fieldlabel']);
															if($datatype == "P") $js['password'][] = array($field['fieldname'], $field['fieldlabel']);
															if( preg_match("/LE/", $datatype) > 0 ) $js['le'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
															if( preg_match("/LT/", $datatype) > 0 ) $js['lt'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
															if( preg_match("/GE/", $datatype) > 0 ) $js['ge'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
															if( preg_match("/GT/", $datatype) > 0 ) $js['gt'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
													}
													$this->uitype_edit->showFieldInput($field , $is_mandatory);
													if($field['uitype_id'] == 5)  $js['date'][] = array($field['fieldname'], $field['fieldlabel']);
													if($field['uitype_id'] == 24) $js['date_from_to'][] = array($field['fieldname'], $field['fieldlabel']);
													if($field['uitype_id'] == 26) $js['time_start_end'][] = array($field['fieldname'], $field['fieldlabel']);
													if($field['uitype_id'] == 13) $load_jqgrid_in_boxy = true;
													if($field['uitype_id'] == 16){
														$js['ckeditor'][] = array($field['fieldname'], $field['fieldlabel']);
														$load_ckeditor = true;
													}
													if($field['uitype_id'] == 11){
														$js['single_upload'][] = array($field['fieldname'], $field['fieldlabel']);
														$load_uploadify = true;
													}
													if($field['uitype_id'] == 20){
														$js['multiple_upload'][] = array($field['fieldname'], $field['fieldlabel'], $field['field_id']);
														$load_uploadify = true;
													}
													if($field['uitype_id'] == 21){
														$js['multiselect'][] = array($field['fieldname'], $field['fieldlabel']);
														$load_multiselect = true;
													}
											        if ($field['uitype_id'] == 39 || $field['uitype_id'] == 29) {
														$js['chosen_autocomplete'][] = array($field['fieldname'], $field['fieldlabel']);
											            $chosen_autocomplete = true;
											        }        													
											endforeach;
									endif;
									if( $fieldgroup['edit_customview'] != "" && $fieldgroup['edit_customview_position'] == 3 ) $this->load->view( $this->userinfo['rtheme'].'/'.$fieldgroup['edit_customview'] );?>
									</div>
									<div class="spacer"></div>
								</div><?php
								//create js validation
								if( $show_wizard_control ){
									$js['fg_id'] = $fieldgroup['fieldgroup_id'];
									$this->load->view($this->userinfo['rtheme'].'/template/edit-wizard-form-js', $js);
									$js = array();
								}
							endforeach;
							//load additional js base on field and validation
							if( $load_ckeditor ) echo CKEditor_script();
							if( $load_jqgrid_in_boxy ) echo jqgrid_in_boxy();
							if( $load_multiselect ) echo multiselect_script();
							if( $load_uploadify ) echo uploadify_script();
					endif;

					if( sizeof($views) > 0 ) :
							foreach($views as $view) :
									$this->load->view($this->userinfo['rtheme'].'/'.$view);
							endforeach;
					endif;
			?>
    	<div class="clear"></div>
    </div>
    
    <div class="form-submit-btn">
        <div class="icon-label-group">
            <div class="icon-label">
                <a rel="record-save-<?php echo $this->module?>" class="icon-16-disk" href="javascript:void(0);" onclick="quick_ajax_save('<?php echo $this->module?>');">
                    <span>Save</span>
                </a>            
            </div>
        </div>
        <div class="or-cancel">
            <span class="or">or</span>
            <a class="cancel" href="javascript:void(0)" onclick="Boxy.get(this).hide().unload()">Cancel</a>
        </div>
    </div>
</form>
 <div class="clear"></div>
<!-- END MAIN CONTENT -->

<script type="text/javascript">
	$(document).ready(function(){
		<?php
			if( isset($js['date']) && sizeof($js['date']) > 0) :
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
						showOn: "button",
						buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
						buttonImageOnly: true,
						buttonText: ''
					});<?
				endforeach;
			endif;?>

		<?php
			if(isset($js['date_from_to']) && sizeof($js['date_from_to']) > 0) :
				foreach($js['date_from_to'] as $param) : ?>
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
						buttonText: ''
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
						buttonText: ''
					});
					<?
				endforeach;
			endif;?>

		<?php
			if( isset($js['integer']) &&  sizeof($js['integer']) > 0) :
				foreach($js['integer'] as $param) : ?>
					 value =  $('input[name="<?php echo $param[0]?>"]').val();
					 value = addCommas(value);
					 $('input[name="<?php echo $param[0]?>"]').addClass('text-right');
					 $('input[name="<?php echo $param[0]?>"]').val(value);
					 $('input[name="<?php echo $param[0]?>"]').keyup( maskInteger );<?
				endforeach;
			endif;?>

		<?php
			if( isset($js['float']) &&  sizeof($js['float']) > 0) :
				foreach($js['float'] as $param) : ?>
					value =  $('input[name="<?php echo $param[0]?>"]').val();
					value = addCommas(value);
					$('input[name="<?php echo $param[0]?>"]').addClass('text-right');
					$('input[name="<?php echo $param[0]?>"]').val(value);
					$('input[name="<?php echo $param[0]?>"]').keyup( maskFloat );<?
				endforeach;
			endif;?>

		<?php
			if(isset($js['password']) && sizeof($js['password']) > 0) : ?>
				$('.change-password').live('click', function (){
					$(this).css('display', 'none');
					$('.'+$(this).attr('field-div')).css('display', '');
				}); <?
			endif;?>

		<?php
			if( isset($js['ckeditor']) && sizeof($js['ckeditor']) > 0) :
				foreach($js['ckeditor'] as $param) : ?>
					$('#<?php echo $param[0]?>').ckeditor();<?
				endforeach;
			endif;?>

		<?php
			if( isset($js['single_upload']) && sizeof($js['single_upload']) > 0) :
				foreach($js['single_upload'] as $param) : ?>
					$('#uploadify-<?php echo $param[0]?>').uploadify({
						'uploader'  : '<?php echo base_url()?>lib/uploadify214/uploadify.swf',
						'script'    : module.get_value('base_url') + "lib/uploadify214/uploadify.php",
						'cancelImg' : '<?php echo base_url()?>lib/uploadify214/cancel.png',
						'folder'    : 'uploads/<?php echo $this->module_link?>',
						'fileExt'	: '*.jpg;*.gif;*.png;*.doc;*.docx;*.xls;*.xlsx;*.pdf;*.txt',
						'fileDesc'  : 'Web Image Files (.JPG, .GIF, .PNG) and Text Documents and Spreadsheets (.DOC, .DOCX, .XLS, .XLSX, .PDF)',
						'auto'      : true,
						'method'	: 'POST',
						'scriptData': {module: "<?php echo $this->module?>", fullpath: module.get_value('fullpath'), path: "uploads/<?php echo $this->module_link?>", field:"<?php echo $param[0]?>"},
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

		<?php 
			if(isset($chosen_autocomplete) && sizeof($chosen_autocomplete) > 0) : 
				foreach($chosen_autocomplete as $param) : ?>
					$('select[id="<?php echo $param[0]?>"]').chosen(); <?
				endforeach;
			endif;?>			
	

		<?php
			if( isset($js['multiple_upload']) && sizeof($js['multiple_upload']) > 0) :
				foreach($js['multiple_upload'] as $param) : ?>
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
							upload_data = upload_data + "&users_id=<?php echo $this->user['users_id']?>";
							upload_data = upload_data + "&upload_path=" + response_data.path;

							$.ajax({
								url: module.get_value('base_url') + module_link +"/file_upload",
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
											var img = '<div class="nomargin image-wrap"><a id="file-<?php echo $param[0]?>-'+ data.upload_id +'" href="<?php echo base_url()?>'+response_data.path+'" width="100px" target="_blank"><img src="<?php echo base_url().$this->userinfo['theme']?>/images/icon-66-file.png"></a><div class="image-delete nomargin multi" field="<?php echo $param[0]?>" upload_id="'+ data.upload_id +'"></div></div>';
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
				endforeach;
			endif;?>

		<?php
			if(isset($js['multiselect']) && sizeof($js['multiselect']) > 0) :
				foreach($js['multiselect'] as $param) : ?>
					$('#multiselect-<?php echo $param[0]?>').multiselect({show:['blind',250],hide:['blind',250],selectedList: 1}); <?
				endforeach;
			endif;?>
	});
	
	function validate_quickform()
	{
		<?php
			if( isset($js['multiselect']) && sizeof($js['multiselect']) > 0) :
				foreach($js['multiselect'] as $param) : ?>
					var temp = $.map($('#multiselect-<?php echo $param[0]?>').multiselect("getChecked"),function( input ){
						return input.value;
					});
					$('input[name="<?php echo $param[0]?>"]').val(temp);
					<?
				endforeach;
			endif; ?>

		<?php
			if( isset($js['mandatory']) && sizeof($js['mandatory']) > 0) :
				foreach($js['mandatory'] as $param) : ?>
					validate_mandatory("<?php echo $param[0]?>", "<?php echo $param[1]?>");	<?
				endforeach;
			endif;?>

		<?php
			if(isset($js['integer']) && sizeof($js['integer']) > 0) :
				foreach($js['integer'] as $param) : ?>
					validate_integer("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>

		<?php
			if(isset($js['float']) && sizeof($js['float']) > 0) :
				foreach($js['float'] as $param) : ?>
					validate_float("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>

		<?php
			if(isset($js['email']) && sizeof($js['email']) > 0) :
				foreach($js['email'] as $param) : ?>
					validate_email("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>

		<?php
			if(isset($js['url']) && sizeof($js['url']) > 0) :
				foreach($js['url'] as $param) : ?>
					validate_url("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>

		<?php
			if(isset($js['password']) && sizeof($js['password']) > 0) :
				foreach($js['password'] as $param) : ?>
					validate_password("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>

		<?php
			if(isset($js['ckeditor']) && sizeof($js['ckeditor']) > 0) :
				foreach($js['ckeditor'] as $param) : ?>
					validate_ckeditor("<?php echo $param[0]?>", "<?php echo $param[1]?>"); <?
				endforeach;
			endif;?>

		<?php
			if(isset($js['le']) && sizeof($js['le']) > 0) :
				foreach($js['le'] as $param) : ?>
					validate_less_or_equal("<?php echo $param[0]?>", "<?php echo $param[1]?>", "<?php echo $param[2]?>"); <?
				endforeach;
			endif;?>

		<?php
			if( isset($js['lt']) && sizeof($js['lt']) > 0) :
				foreach($js['lt'] as $param) : ?>
					validate_less_than("<?php echo $param[0]?>", "<?php echo $param[1]?>", "<?php echo $param[2]?>"); <?
				endforeach;
			endif;?>

		<?php
			if(isset($js['ge']) && sizeof($js['ge']) > 0) :
				foreach($js['ge'] as $param) : ?>
					validate_greater_or_equal("<?php echo $param[0]?>", "<?php echo $param[1]?>", "<?php echo $param[2]?>"); <?
				endforeach;
			endif;?>

		<?php
			if(isset($js['gt']) && sizeof($js['gt']) > 0) :
				foreach($js['gt'] as $param) : ?>
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