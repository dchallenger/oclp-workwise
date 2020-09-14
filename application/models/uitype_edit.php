<?php
require_once (APPPATH . 'models/uitype_base.php');

class uitype_edit extends Uitype_base
{
	function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
	}

	function showFieldInput( $field = array(), $mandatory = false, $use_tabindex = false, $readonly = false )
	{
		if (!$use_tabindex) {
			$field['tabindex'] = '';
		} else {							
			$field['tabindex'] = 'tabindex="' . $field['tabindex'] . '"';	
		}

		if( $field['visible'] == 1 ) :
			if( in_array( $field['uitype_id'], array(1,2,3,4,5,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41)) ){
				$display = display_field( 'edit', $field['display'] ); ?>
				<div class="<?=($field['uitype_id'] != 16) ? 'form-item' : ''?> <?=($field['sequence'] % 2 == 0 ? 'even' : 'odd') ?> <?php echo $display ? '' : 'hidden'?>">
                    <?php if ($field['uitype_id'] == 30):?>
                        <?=$this->field($field['uitype_id'], $field['field_id'], $field['fieldname'], $field['value'], $field['tabindex']);?>
                        <span><?=$field['fieldlabel']?></span>
                        <?=($mandatory ? '<span class="red font-large">*</span>' : '')?>
                        <?=($field['uitype_id'] == 10 ? '<div style="height:10px"></div><span class="'.$field['fieldname'].'-field-div profile_move_down" '. ($this->input->post('record_id') == -1 ? '' : 'style="display:none"') .'>Confirm:</span>' : '')?>
                    <?php ;else: 
                            if ($field['uitype_id'] == 16) {
                                $class = '';
                            } else {
                                $class = 'label-desc gray';
                            }
                        ?>
                        <label for="<?=$field['fieldname']?>" class="<?=$class?>">
                                <?=$field['fieldlabel']?>:
                                <?=($mandatory ? '<span class="red font-large">*</span>' : '')?>
                                <?=($field['uitype_id'] == 10 ? '<div style="height:10px"></div><span class="'.$field['fieldname'].'-field-div profile_move_down" '. ($this->input->post('record_id') == -1 ? '' : 'style="display:none"') .'>Confirm:</span>' : '')?>
                        </label>
                        <?=$this->field($field['uitype_id'], $field['field_id'], $field['fieldname'], $field['value'], $field['tabindex'], $readonly);?>
                    <?php endif;?>
                </div><?
			}
			else if( in_array($field['uitype_id'], array(6,7,8,9)) ){
				if($field['uitype_id'] == 6 || $field['uitype_id'] == 8){?>
					<div class="form-item <?=($field['sequence'] % 2 == 0 ? 'even' : 'odd') ?>">
					<label for="<?=$field['fieldname']?>" class="label-desc gray">
						<?=$field['fieldlabel']?>:
						<?=($mandatory ? '<span class="red font-large">*</span>' : '')?>
					</label>
					<?=$this->field($field['uitype_id'], $field['field_id'], $field['fieldname'], $field['value'], $field['tabindex'], $readonly);?>
					<?
				}
				else{?>
					<?=$this->field($field['uitype_id'], $field['field_id'], $field['fieldname'], $field['value'], $field['tabindex'], $readonly);?>
					</div><?
				}
			}
			else if( in_array($field['uitype_id'], array(16)) )
			{
				echo $this->field($field['uitype_id'], $field['field_id'], $field['fieldname'], $field['value'], $field['tabindex']);
			}				
   		endif;
  	} //end function showFieldDetail

	function showFieldInputBlank( $field = array(), $mandatory = false, $use_tabindex = false, $readonly = false )
	{
		if (!$use_tabindex) {
			$field['tabindex'] = '';
		} else {							
			$field['tabindex'] = 'tabindex="' . $field['tabindex'] . '"';	
		}

		if( $field['visible'] == 1 ) :
			if( in_array( $field['uitype_id'], array(1,2,3,4,5,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41)) ){
				$display = display_field( 'edit', $field['display'] ); ?>
				<div class="<?=($field['uitype_id'] != 16) ? 'form-item' : ''?> <?=($field['sequence'] % 2 == 0 ? 'even' : 'odd') ?> <?php echo $display ? '' : 'hidden'?>">
                    <?php if ($field['uitype_id'] == 30):?>
                        <?=$this->field($field['uitype_id'], $field['field_id'], $field['fieldname'], '', $field['tabindex']);?>
                        <span><?=$field['fieldlabel']?></span>
                        <?=($mandatory ? '<span class="red font-large">*</span>' : '')?>
                        <?=($field['uitype_id'] == 10 ? '<div style="height:10px"></div><span class="'.$field['fieldname'].'-field-div profile_move_down" '. ($this->input->post('record_id') == -1 ? '' : 'style="display:none"') .'>Confirm:</span>' : '')?>
                    <?php ;else: 
                            if ($field['uitype_id'] == 16) {
                                $class = '';
                            } else {
                                $class = 'label-desc gray';
                            }
                        ?>
                        <label for="<?=$field['fieldname']?>" class="<?=$class?>">
                                <?=$field['fieldlabel']?>:
                                <?=($mandatory ? '<span class="red font-large">*</span>' : '')?>
                                <?=($field['uitype_id'] == 10 ? '<div style="height:10px"></div><span class="'.$field['fieldname'].'-field-div profile_move_down" '. ($this->input->post('record_id') == -1 ? '' : 'style="display:none"') .'>Confirm:</span>' : '')?>
                        </label>
                        <?=$this->field($field['uitype_id'], $field['field_id'], $field['fieldname'], '', $field['tabindex'], $readonly);?>
                    <?php endif;?>
                </div><?
			}
			else if( in_array($field['uitype_id'], array(6,7,8,9)) ){
				if($field['uitype_id'] == 6 || $field['uitype_id'] == 8){?>
					<div class="form-item <?=($field['sequence'] % 2 == 0 ? 'even' : 'odd') ?>">
					<label for="<?=$field['fieldname']?>" class="label-desc gray">
						<?=$field['fieldlabel']?>:
						<?=($mandatory ? '<span class="red font-large">*</span>' : '')?>
					</label>
					<?=$this->field($field['uitype_id'], $field['field_id'], $field['fieldname'], '', $field['tabindex'], $readonly);?>
					<?
				}
				else{?>
					<?=$this->field($field['uitype_id'], $field['field_id'], $field['fieldname'], '', $field['tabindex'], $readonly);?>
					</div><?
				}
			}
			else if( in_array($field['uitype_id'], array(16)) )
			{
				echo $this->field($field['uitype_id'], $field['field_id'], $field['fieldname'], '', $field['tabindex']);
			}				
   		endif;
  	} //end function showFieldDetail

	function field( $type = 0, $field_id = 0, $name = "", $value = "", $tabindex = '', $readonly = false)
	{		
		switch($type){
			case 1: // Textfield
				return '<div class="text-input-wrap"><input type="text" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text"/></div>';
				break;
			case 2: // Textarea
				return '<div class="textarea-input-wrap"><textarea rows="5" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" class="input-textarea">'. $value .'</textarea></div>';
				break;
			case 3: // Dropdown
				return $this->picklist( $field_id, $name, $value, false, $tabindex );
				break;
			case 4: // Yes/No
				return '<div class="radio-input-wrap">
						<input type="radio" ' . $tabindex . ' name="'.$name.'" id="'.$name.'-yes" value="1" class="input-radio" '.($value == 1 ? 'checked="checked"' : "").'/>
						<label for="'.$name.'-yes" class="check-radio-label gray">Yes</label>
						<input type="radio" name="'.$name.'" id="'.$name.'-no" value="0" class="input-radio" '.(empty( $value ) ? 'checked="checked"' : "").'/>
						<label for="'.$name.'-no" class="check-radio-label gray">No</label>
					</div>';
				break;
			case 5: // Date
				if($value == '0000-00-00') $value = '';
				if( $value != "" )
				{
					$temp = explode('-', $value);
					$value = $temp[1].'/'.$temp[2].'/'.$temp[0];
				}
				return '<div class="text-input-wrap">
				<input type="hidden" value="'. $value .'" name="'.$name.'" id="'.$name.'" />
				<input type="text" ' . $tabindex . ' name="'.$name.'-temp" id="'.$name.'-temp" value="'. $value .'" class="input-text datepick datepicker" readonly/></div>';
				break;                                
			case 6: // (Salutation then Firstname: Salutaion part)
				return '<div class="select-input-wrap">
					<select ' . $tabindex . ' name="'.$name.'" id="'.$name.'" style="width:15%">
						<option value="Mr." '.($value == "Mr." ? 'selected' : "").'>Mr.</option>
						<option value="Ms." '.($value == "Ms." ? 'selected' : "").'>Ms.</option>
						<option value="Mrs." '.($value == "Mrs." ? 'selected' : "").'>Mrs.</option>
						<option value="Atty." '.($value == "Atty." ? 'selected' : "").'>Atty.</option>
					</select>
					';
				break;
			case 7: // (Salutation then Firstname: first name part)
				return '<input type="text" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text" style="width:75%"/></div>';
				break;
			case 8: // (Last name and auxilliary: lastname part)
				return '<div class="text-input-wrap"><input type="text" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text" style="width:62%"/>';
				break;
			case 9: // (Last name and auxilliary: aux part)
				return '<input type="text" name="'.$name.'" id="'.$name.'" ' . $tabindex . ' value="'. $value .'" class="input-text" style="width:25%"/></div>';
				break;
			case 10: // Password
				$str = '<div class="text-input-wrap">';
				if($this->input->post('record_id') == -1 )
				{
					$str .= '<input type="password" name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text" ' . $tabindex . '/><br/>
							 <input type="password" name="'.$name.'-confirm" id="'.$name.'-confirm" value="" class="input-text" ' . $tabindex . '/>';
				}
				else{
					$str .= ' <a href="javascript:void(0)" class="other-link change-password" field-div="'.$name.'-field-div">Change password?</a>
					<span class="'.$name.'-field-div" style="display:none">
						<input type="password" name="'.$name.'" id="'.$name.'" value="" class="input-text" ' . $tabindex . '/>
						<div style="height:10px"></div>
						<input type="password" name="'.$name.'-confirm" id="'.$name.'-confirm" value="" class="input-text" ' . $tabindex . '/>
					</span>
					';
				}
				$str .=	'</div>';
				return $str;
				break;
			case 11: // single upload				
				$html = '<div class="text-input-wrap">
							<div id="error-'.$name.'"></div>';
				$html .= '<div class="nomargin image-wrap" id="'.$name.'-upload-container">';
				if (!empty($value)) {

					$path_info = pathinfo(base_url() . $value);
					if( in_array( $path_info['extension'], array('jpeg', 'jpg', 'JPEG', 'JPG', 'gif', 'GIF', 'png', 'PNG', 'bmp', 'BMP') ) )
					{
						$html .= '<img id="file-'.$name.'" src="'.(!empty($value) ? base_url() . $value : base_url() . $this->userinfo['theme']. '/images/no-photo.jpg').'" width="100">
									<div class="image-delete nomargin" field="'.$name.'"></div>
								';
					}
					else{
						$html .= '<a id="file-'. $name .'" href="'.base_url() . $value .'" target="_blank"><img src="'. base_url() .$this->userinfo['theme'].'/images/file-icon-md.png"></a>
								<div class="image-delete nomargin" field="'.$name.'"></div>
								';
					}
				}							
				$html .= '</div>
				<div class="clear"></div>';
				$html .= '<input id="'.$name.'" name="'.$name.'" type="hidden" value="'.(!empty($value) ? $value : '').'" />
							<input id="uploadify-'.$name.'" name="uploadify-'.$name.'" rel="'.$name.'" type="file" class="single-upload"/>
						</div>';				

				return $html;
				break;
			case 20: // multiple image upload
				return $this->multiple_upload( $field_id, $name, $value );
				break;
			case 12: // position Left Right Center Top
				return '<div class="select-input-wrap">
						<select name="'.$name.'" ' . $tabindex . '>
							<option value="">Select...</option>
							<option value="top" '.($value == "top" ? 'selected' : "").'>Top</option>
							<option value="left" '.($value == "left" ? 'selected' : "").'>Left</option>
							<option value="center" '.($value == "center" ? 'selected' : "").'>Center</option>
							<option value="right" '.($value == "right" ? 'selected' : "").'>Right</option>
						</select>
					</div>';
				break;
			case 13: // Module Boxy
				return $this->listview_boxy( $field_id, $name, $value, $tabindex, $readonly );
				break;
			case 14: // field group dropdown
				if($value == ""){
					return '<div class="select-input-wrap fieldgroup-div"></div>';
				}
				else{
					return '<div class="select-input-wrap fieldgroup-div">'. $this->fieldGroup_ddlb($value) .'</div>';
				}
				break;
			case 15: // Textfield datatype with description
				return '<div class="text-input-wrap">
					<input type="text" name="'.$name.'" id="'.$name.'" ' . $tabindex . ' value="'. $value .'" class="input-text"/>

					<p id="datatype-tooltip" class="form-item-description">
					    Should be separated by a ~ (tilde). Possible values: 
					    <span class="nobr"><strong>V</strong> - Varchar (any character)</span> / 
				    	<span class="nobr"><strong>M</strong> - Mandatory</span> / 
				    	<span class="nobr"><strong>O</strong> - Optional</span> / 
				    	<span class="nobr"><strong>R</strong> - Read Only and Auto-generated value fields</span> / 
				    	<span class="nobr"><strong>E</strong> - Email (if not empty, must be a valid email)</span> / 
				    	<span class="nobr"><strong>U</strong> - URL (if not empty, must be valid URL)</span> / 
				    	<span class="nobr"><strong>I</strong> - Integer</span> / 
				    	<span class="nobr"><strong>F</strong> - Float</span> / 
				    	<span class="nobr"><strong>GE</strong> - Greater or equal</span> / 
				    	<span class="nobr"><strong>GT</strong> - Greater than</span> / 
				    	<span class="nobr"><strong>LE</strong> - Less or Equal</span> / 
				    	<span class="nobr"><strong>LT</strong> - Less Than </span> / 
				    	<span class="nobr"><strong>N</strong> - Numeric.</span> /
				    	<span class="nobr"><strong>UN</strong> - Unique </span>
					</p>
				</div>';
				break;
			case 16: // WYSIWYG
				echo '<div class="textarea-input-wrap" align="center">';
				$this->load->library ('ckeditor');
				$this->load->library ('ckfinder');
				$$name = new CKEditor();
				$$name->basePath = base_url() . 'lib/ckeditor3.6.1/';
				$this->ckfinder->SetupCKEditor ( $$name );
				$$name->editor( $name, $value );
				echo '</div>';
				return "";
				break;
			case 17: // Two/Single Column Layout
				return '<div class="radio-input-wrap"><input type="radio" ' . $tabindex . ' name="'.$name.'" id="'.$name.'-single" value="1" class="input-radio" '.($value == 1 ? 'checked="checked"' : "").'/><label for="'.$name.'-single" class="check-radio-label gray">Single Column</label><input type="radio" name="'.$name.'" id="'.$name.'-two" value="0" class="input-radio" '.($value == 0 ? 'checked="checked"' : "").'/><label for="'.$name.'-two" class="check-radio-label gray">Two Column</label>
					</div>';
				break;
			case 18: // Picklist Type
				return '<div class="radio-input-wrap">
					<input type="radio" name="'.$name.'" id="'.$name.'-query" ' . $tabindex . ' value="Query" class="input-radio" '.($value == "Query" ? 'checked="checked"' : "").'/>
					<label for="'.$name.'-query" class="check-radio-label gray">Query</label>
					<input type="radio" name="'.$name.'" id="'.$name.'-table" value="Table" class="input-radio" '.($value == "Table" ? 'checked="checked"' : "").'/>
					<label for="'.$name.'-table" class="check-radio-label gray">Table</label>
					<input type="radio" name="'.$name.'" id="'.$name.'-fields" value="Fields" class="input-radio" '.($value == "Fields" ? 'checked="checked"' : "").'/>
					<label for="'.$name.'-fields" class="check-radio-label gray">Fields</label>					
					<input type="radio" name="'.$name.'" id="'.$name.'-fields" value="Function" class="input-radio" '.($value == "Function" ? 'checked="checked"' : "").'/>
					<label for="'.$name.'-fields" class="check-radio-label gray">Function</label>
					</div>';
				break;
			case 19: // Days Multi-select
				if($value != "")
				{
					$value = unserialize($value);
				}
				else{
					$value = array();
				}
				$str = '<div class="text-input-wrap">';
				for($i=1; $i<8 ; $i++){
					$str .= '<input type="checkbox" name="'.$name.'[]" ' . $tabindex . ' value="'.$i.'" '.(in_array($i, $value) ? 'checked="checked"': "").'> '.int_to_day($i, 'full')."<br/>";
				}
				$str .= '</div>';
				return $str;
				break;
			case 21: // Multi-select
				return $this->multiselect($field_id, $name, $value, $tabindex);
				break;
			case 22:
				$dirreader = $this->db->get_where('directory_reader', array('directory_reader_id' => $field_id));

				if($dirreader->num_rows() > 0 && $dirreader->num_rows() == 1)
				{
					$dirresult	= $dirreader->row_array();
					$dir_path	= $dirresult['directory_path'];
					$file_ext	= $dirresult['file_ext'];

					//read icons directory
					$this->load->helper('file');
					$files = get_filenames($dir_path);

					$drop_down = '<div class="select-input-wrap"><select name="'.$name.'" ' . $tabindex . '>';
					foreach($files as $file)
					{
						if($file == $value)
							$selected = 'selected';
						else
							$selected = '';

						if( !empty($file_ext) )
						{
							if( strpos( strtolower($file), strtolower( '.'.$file_ext ) ) )
							{
								$icons[$file] = $file;
								$drop_down .= '<option value="'.$file.'" '.$selected.'>'.$file.'</option>';
							}
						}
						else{
							$icons[$file] = $file;
							$drop_down .= '<option value="'.$file.'" '.$selected.'>'.$file.'</option>';
						}
					}

					return $drop_down .= '</select></div>';
				}
				else
				{
					return "Directory associated with field_id ". $field_id ." was not found.";
				}
				break;
			case 23:
				return '<div class="text-input-wrap"><input type="text" name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text" ' . $tabindex . ' readonly="readonly"/></div>';
				break;
			case 24: // Date from - Date to
				$value_from = "";
				$value_to = "";

				if( $value != "" )
				{
					$temp = explode('to', $value);

					if( $temp[0] == " "){
						$value_from = "";
					}
					else{
						$temp[0] = rtrim($temp[0]);
						$value_from = explode('-', $temp[0]);
						$value_from = month_to_int( $value_from[1], true ). '/' .$value_from[0]. '/' . $value_from[2];
					}

					if($temp[1] == " ")
					{
						$value_to = "";
					}
					else{
						$temp[1] = ltrim($temp[1]);
						$value_to = explode('-', $temp[1]);
						$value_to = month_to_int( $value_to[1], true ). '/' .$value_to[0]. '/' . $value_to[2];
					}
				}
				return '<div class="text-input-wrap">
				<input type="hidden" value="'. $value_from .'" name="'.$name.'_from" id="'.$name.'_from" />
				<input type="text" ' . $tabindex . ' name="'.$name.'-temp-from" id="'.$name.'-temp-from" value="'. $value_from .'" class="input-text datepicker disabled" disabled="disabled"/>
				&nbsp;&nbsp;&nbsp;<span class="to">to</span>&nbsp;&nbsp;&nbsp;
				<input type="hidden" value="'. $value_to .'" name="'.$name.'_to" id="'.$name.'_to" />
				<input type="text" name="'.$name.'-temp-to" id="'.$name.'-temp-to" value="'. $value_to .'" class="input-text datepicker disabled" disabled="disabled"/>

				</div>';
				break;
			case 26: //Time From - Time in 24hrs format UI
				if($value!= "")
				{
					$temp = explode(' to ', $value);
					$value_start = explode(':', $temp[0]);
					$value_start_hh = $value_start[0];
					$value_start_mm = $value_start[1];
					
					$value_end = explode(':', $temp[1]);
					$value_end_hh = $value_end[0];
					$value_end_mm = $value_end[1];
				}
				else
				{
					$value_start= "";
					$value_end = "";
					$value_start_hh = "";
					$value_start_mm = "";
					$value_end_mm = "";
					$value_end_hh = "";
				}
				
				
				$time = '<div class="text-input-wrap">';
				$time .= '<select name="'.$name.'_start_hh" ' . $tabindex . '>';
				
				for($i=0; $i<=23; $i++)
				{
					if($i < 10) $i = '0'.$i;
					
					$time .='<option value="'.$i.'" ';
					if($value_start_hh == $i)
					{
						$time .= 'selected';
					}
					$time .= '>'.$i.'</option>';
				}
				$time .= '</select>';
				
				$time .= ' : <select name="'.$name.'_start_mm">';
				for($i=0; $i<=59; $i++)
				{
					if($i < 10) $i = '0'.$i;
					
					$time .='<option value="'.$i.'" ';
					if($value_start_mm == $i)
					{
						$time .= 'selected';
					}
					$time .= '>'.$i.'</option>';
				}
				
				$time .= '</select>';
				$time .= '&nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;';
				
				
				$time .= '<select name="'.$name.'_end_hh">';
				for($i=0; $i<=23; $i++)
				{
					if($i < 10) $i = '0'.$i;
					$time .='<option value="'.$i.'" ';
					if($value_end_hh == $i)
					{
						$time .= 'selected';
					}
					$time .= '>'.$i.'</option>';
				}
				
				$time .= '</select>';
				$time .= ' : <select name="'.$name.'_end_mm">';
				for($i=0; $i<=59; $i++)
				{
					if($i < 10) $i = '0'.$i;
					$time .='<option value="'.$i.'" ';
					if($value_end_mm == $i)
					{
						$time .= 'selected';
					}
					$time .= '>'.$i.'</option>';
				}
				
				$time .= '</select>';
				$time .= '</div>';
	
				return $time;
				break;
			case 27: // Male/Female
				return '<div class="radio-input-wrap"><input type="radio" ' . $tabindex . ' name="'.$name.'" id="'.$name.'-male" value="male" class="input-radio" '.($value == 'male' ? 'checked="checked"' : "").'/><label for="'.$name.'-male" class="check-radio-label gray">Male</label><input type="radio" name="'.$name.'" id="'.$name.'-female" value="female" class="input-radio" '.( $value == 'female' ? 'checked="checked"' : "").'/><label for="'.$name.'-female" class="check-radio-label gray">Female</label>
					</div>';
				break;
			case 5: // Date
				if( $value != "" )
				{
					$temp = explode('-', $value);
					$value = $temp[1].'/'.$temp[2].'/'.$temp[0];
				}
				return '<div class="text-input-wrap">
				<input type="hidden" value="'. $value .'" name="'.$name.'" id="'.$name.'" />
				<input type="text" ' . $tabindex . ' name="'.$name.'-temp" id="'.$name.'-temp" value="'. $value .'" class="input-text datepicker disabled" disabled="disabled"/></div>';
				break;
            case 28: // Database tables dropdown.
                    $tables = $this->db->list_tables();

                    foreach ($tables as $key => $table) {
                    	$tables[$key] = str_replace($this->db->dbprefix, '', $table);
                    }

                    $return = '<div class="select-input-wrap">';
                    $return .= '<select ' . $tabindex . ' id="'. $name .'" name="' . $name . '">';
                    $return .= '<option value="">Select&hellip;</option>';

                    foreach ($tables as $table) {
                    	$selected = '';
                    	if ($table == $value) {
                    		$selected = 'selected';
                    	}

                    	$return .= '<option value="' . $table . '" ' . $selected . '>' . $table . '</option>';
                    }

                    $return .= '</select></div>';

                    return $return;
                    break;
            case 29: // Database relationship dropdown.
                    $options = array ('One-One', 'One-Many');
                    return form_dropdown($name, $options, $value);
                    break;
            case 30: // Boolean checkbox.
                    return form_checkbox($name, 1, ($value == 1), $tabindex);                            
                    break;
            case 31:
			case 32: // DateTime			
				if($value == '0000-00-00 00:00:00') $value = '';
				if($value == '1970-01-01 08:00:00') $value = '';
				if($value == '0') $value = '';

				if( $value != "" )
				{
                    $value = date($this->config->item('edit_datetime_format'), strtotime($value));
				}
				return '<div class="text-input-wrap">				
				<input type="text" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text datetimepicker" readonly/></div>';
				break;   
            case 33: // jquery UI Time 
                $value = ($value == '00:00:00') ? '' : date($this->config->item('display_time_format'), strtotime($value));
                return '<div class="text-input-wrap">
				<input type="text" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text timepicker" readonly/></div>';
				break;   				
            case 34: // jquery UI Month year only
                if($value == '00:00:00') $value = '';
                return '<div class="text-input-wrap">				
				<input type="text" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text month-year" readonly/></div>';
				break; 		
			case 37: // jquery UI Minute Second Picker
                $value = ($value == '00:00:00') ? '' : date($this->config->item('display_mmss_format'), strtotime($value));;
                return '<div class="text-input-wrap">
				<input type="text" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text minutesecondpicker" readonly/></div>';
				break;
			case 38: // jquery UI Start - End time picker
				if($value!= "")
				{
					$temp = explode(' to ', $value);
					$value_start = $temp[0];					
					$value_end = $temp[1];
				}
				else
				{
					$value_start= "";
					$value_end = "";
				}
                $value_start = ($value_start == '00:00:00') ? ' ' : date($this->config->item('display_time_format'), strtotime($value_start));
                $value_end = ($value_end == '00:00:00') ? ' ' : date($this->config->item('display_time_format'), strtotime($value_end));
                return '<div class="text-input-wrap">
				<input type="text" ' . $tabindex . ' name="'.$name.'_start" id="'.$name.'_start" value="'. $value_start .'" class="input-text start_timepicker" style="width:35%" readonly/> to 
				<input type="text" name="'.$name.'_end" id="'.$name.'_end" value="'. $value_end .'" class="input-text end_timepicker" style="width:35%" readonly/></div>';
				break;  				
			case 35: //number range from - to
				$value_from = 0;
				$value_to = 0;
				if(!empty( $value )){
					$values = explode("to", $value);
					$value_from = trim($values[0]);
					$value_to = trim($values[1]);
				}				
				return '<div class="text-input-wrap">
				<input type="text" ' . $tabindex . ' class="input-text text-right" value="'. $value_from .'" name="'.$name.'_from" id="'.$name.'_from" style="width:35%"/>
				&nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;
				<input type="text" class="input-text text-right" value="'. $value_to .'" name="'.$name.'_to" id="'.$name.'_to" style="width:35%"/>
				</div>';
				break; 
			case 25: //Place holder/Label
				return '<div class="text-input-wrap"></div>';
				break;	                          
			case 36:
				$this->db->where('field_id', $field_id);

				$result = $this->db->get('field_options')->row();

				$options = explode(',', $result->options);

				$fields = '';
				foreach ($options as $key => $option) {
					$option = trim($option);
					$fields .= form_radio($name, $key + 1, ($value == $key + 1), $tabindex) . $option . '<br />';
				}

				return $fields;
				break;			
			case 39: // Autocomplete
				
				$this->db->where('field_id', $field_id);
				$this->db->where('deleted', 0);
				$this->db->limit(1);

				$result = $this->db->get('field_autocomplete');				

				$params = 'id="'. $name . '" ' . $tabindex;

				if ($result->num_rows() > 0) {
					$row = $result->row();
					if ($row->type == 'Query') {
						$results = $this->db->query(str_replace('{dbprefix}', $this->db->dbprefix, $row->table))->result_array();
					} else if ($row->type == 'Function') {
						$module = $this->hdicore->get_module($this->module_id);
						
						if (!is_loaded($module->class_name)) {
							$path   = explode('/', $module->class_path);

							unset($path[count($path) - 1]);
							load_class($module->class_name, 'controllers/' . implode('/', $path));
						}
						
						if (method_exists($module->class_name, $row->table)) {							
							$results = call_user_func(array($module->class_name, $row->table));
						} else {
							$results = $this->{$row->table}();
						}
					} else {
						$this->db->where('deleted', 0);
						$results = $this->db->get($row->table)->result_array();						
					}
					
					if ($row->multiple) {
						$params .= ' multiple';
						$value = explode(',', $value);
						$options = array();
					} else {
						$options = array(' ' => ' ');
					}
					
					foreach ($results as $option) {
						$labels = explode(',', $row->label);

						$label = array();
						foreach ($labels as $l) {
							$label[] = $option[$l];
						}							
						
						if (trim($row->group_by) != '') {
							$options[$option[$row->group_by]][$option[$row->value]] = implode(' ', $label);
						} else {
							$options[$option[$row->value]] = implode(' ', $label);
						}
					}
				}
				
				return '<div class="select-input-wrap">'. form_dropdown($name, $options, $value, $params) . '</div>';
				break;
			case 40: // DateTime			
				$value_from = "";
				$value_to = "";

				if( $value != "" )
				{
					$temp = explode('to', $value);

					if( $temp[0] == " "){
						$value_from = "";
					}
					else{
						$value_from = date('m/d/Y h:i a', strtotime( $temp[0]));
					}

					if($temp[1] == " ")
					{
						$value_to = "";
					}
					else{
						$value_to = date('m/d/Y h:i a', strtotime( $temp[1]));
					}
				}
				return '<div class="text-input-wrap">				
				<input type="text" ' . $tabindex . ' name="'.$name.'_from" id="'.$name.'_from" value="'. $value_from .'" class="datepicker input-text datetimepicker" readonly/>
				 to 
				<input type="text" ' . $tabindex . ' name="'.$name.'_to" id="'.$name.'_to" value="'. $value_to .'" class="datepicker input-text datetimepicker" readonly/></div>';
				break;
			case 41: // jquery UI Hour Minute Second Picker
                $value = ($value == '00:00:00') ? '' : date($this->config->item('display_hhmmss_format'), strtotime($value));;
                return '<div class="text-input-wrap">
				<input type="text" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text minutesecondpicker" readonly/></div>';
				break;				 
			default:
				return '<div class="text-input-wrap"><input type="text" name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text"/></div>';
		}
	}

	function fieldGroup_ddlb( $fieldgroup_id = 0 )
	{
		$fg = $this->db->get_where('fieldgroup', array('fieldgroup_id' => $fieldgroup_id));
		if($this->db->_error_message() == "")
		{
			if( $fg->num_rows() > 0 && $fg->num_rows() == 1 )
			{
				$fg = $fg->row_array();
				$module_id = $fg['module_id'];

				//create dropdown
				$this->db->order_by('sequence');
				$field_group = $this->db->get_where('fieldgroup', array('module_id' => $module_id));
				if( $this->db->_error_message() == "" )
				{
					if( $field_group->num_rows() > 0 )
					{
						$field_group = $field_group->result();
						$str = '<select name="fieldgroup_id" id="fieldgroup_id">';
						foreach($field_group as $row)
						{
							$selected = "";
							if( $row->fieldgroup_id == $fieldgroup_id ) $selected = 'selected';
							$str .= '<option value="'. $row->fieldgroup_id .'" '. $selected .'>'. $row->fieldgroup_label .'</option>';
						}
						$str .= '</select>';
						return $str ;
					}
				}
				else{
					return $this->db->_error_message();
				}
			}
			else{
				return "Fieldgroup width fieldgroup_id ". $fieldgroup_id ." was not found.";
			}
		}
		else{
			return $this->db->_error_message();
		}
	}

	function listview_boxy( $field_id = 0, $name = "", $value = "", $tabindex, $readonly )
	{
		if( empty($value) || $value=='undefined'){
			//new module
			$value = "";
			$valuename = "";
		}
		else{
			//get field_module_link
			$this->db->select('a.module_id, a.column, field.table, module.key_field');
			$this->db->from('field_module_link a');
			$this->db->join('module', 'module.module_id = a.module_id', 'left');
			$this->db->join('field', 'field.module_id = a.module_id', 'left');
			$this->db->where(array('a.field_id' => $field_id));

			$module = $this->db->get();
			
			if( $this->db->_error_message() == "" )
			{
				if($module->num_rows() > 0)
				{
					$module = $module->row_array();
					$module_id = $module['module_id'];
					$column = $module['column'];
					$table = $module['table'];
					$key_field = $module['key_field'];
					$this->db->select($column);
					$valuename = $this->db->get_where($table, array($key_field => $value));
					if( $this->db->_error_message() == "" )
					{
						if($valuename->num_rows() > 0)
						{
							$valuename = $valuename->row_array();
							if( strpos($column, ',') )
							{
								$temp_val = array();
								$column_lists = explode( ',', $column);
								foreach($column_lists as $col_index => $column)
								{
									$temp_val[] = $valuename[$column];
								}
								$valuename = implode(' ', $temp_val);
							}
							else{
								if(sizeof(explode(' AS ', $column)) > 1 ){
									$as_part = explode(' AS ', $column);
									$column = strtolower( trim( $as_part[1] ) );
								}
								else if(sizeof(explode(' as ', $column)) > 1 ){
									$as_part = explode(' as ', $column);
									$column = strtolower( trim( $as_part[1] ) );
								}
								$valuename = $valuename[$column];
							}
						}
						else{
							$valuename = "";
							$value = "";
						}
					}
					else{
						return $this->db->_error_message();
					}
				}
			}
			else{
				return $this->db->_error_message();
			}
		}

		if( $this->module != "picklist" )
		{
			$width = '80';
		}
		else{
			$width = '90';
		}
         
		$str = '<div class="text-input-wrap">
				<input type="hidden" name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text"/>
				<input type="text" name="'.$name.'-name" id="'.$name.'-name" value="'. $valuename .'" class="input-text disabled" style="width:'.$width.'%" disabled="disabled"/>';
		if( $this->module != "picklist" && !$readonly ) $str .= '<span class="icon-group">
		<a class="icon-button icon-16-add" href="javascript:void(0);" onclick="getRelatedModule(\''.$field_id.'\', \''. $name .'\')"></a><a class="icon-button icon-16-minus" href="javascript:void(0);" onclick="clearField(\''. $name .'\')"></a>
		</span>';
		return 	$str.'</div>';
	}

	function picklist( $field_id = 0, $name = "", $value = "", $disabled = false, $tabindex = '')
	{
		//get detail of dropdown
		$picklist = $this->db->get_where('picklist', array('field_id' => $field_id));
		if( $picklist->num_rows() )
		{
			$picklist = $picklist->row_array();
			$id_column = $picklist['picklist_name'].'_id';
			$name_column = $picklist['picklist_name'];
			$picklist_table = $picklist['picklist_table'];
			$picklist_type = $picklist['picklist_type'];
			$picklist_where = $picklist['where'];

			//get actual values from table
			if($picklist_type == "Table")
			{
				$this->db->select($id_column.', '.$name_column);
				$this->db->from($picklist_table);
				$this->db->order_by($name_column);
				$this->db->where(array('deleted' => 0));
				if( !empty($picklist_where) ) $this->db->where($picklist_where);
				$picklistvalues = $this->db->get()->result_array();
			} else if ($picklist_type == 'Function') {						
				$picklistvalues = $this->{$row->table}();
			} else if($picklist_type == "Query"){
				$picklistvalues = $this->db->query(str_replace('{dbprefix}', $this->db->dbprefix, $picklist_table))->result_array();			
			}

			if( $this->db->_error_message() == "" )
			{
				$str = '<div class="select-input-wrap">';
				$str .= $disabled ?	'<input type="checkbox" name="toggle-'. $name .'"/> ' : '';
				$str .= '<select ' . $tabindex . ' name="'.$name.'" id="'.$name.'" '. ( $disabled ? 'disabled="disable"' : '') .'><option value="">Select&hellip;</option>';				
				foreach($picklistvalues as $index => $option)
				{					
					$str .=  '<option value="'.$option[$id_column].'" '.($value == $option[$id_column] ? 'selected' : '').'>'. $option[$name_column] .'</option>';
				}
				$str .= '
					</select>
				</div>';
				return $str;
			}
			else{
				return '<div class="text-input-wrap">'. $this->db->_error_message() .'</div>';
			}
		}
		else{
			return '<div class="text-input-wrap">Error! Picklist field not defined</div>';
		}
	}

	function multiselect( $field_id = 0, $name = "", $value = "", $tabindex )
	{
		$str = '<div class="multiselect-input-wrap">';
		//get the multiselect details
		$multiselect = $this->db->get_where('field_multiselect', array('field_id' => $field_id));
		if( $this->db->_error_message() == "" )
		{
			if($multiselect->num_rows() > 0)
			{
				$multiselect = $multiselect->row_array();
				$table = $multiselect['table'];
				$id_column = $multiselect['id_column'];
				if( strpos($id_column, '.') ){
					$id_column = explode('.', $id_column);
					$id_column = $id_column[1];	
				}
				$name_column = $multiselect['name_column'];
				$where_cond = $multiselect['where_condition'];
				$type = $multiselect['type'];
				$group_by = $multiselect['optgroup_column'];
				
				if(  $type == "Table" ){
					$this->db->select( $id_column .', '. $name_column );
					if( $table != 'month' && $table != 'day' && $table != 'time_24hr_format' && $table != 'jo_status' ) $this->db->order_by($name_column);
					if(!empty($where_cond)) $this->db->where( $where_cond );
					$options = $this->db->get_where( $table, array('deleted' => 0 ));
				} else if ($type == 'Function') {
					$module = $this->hdicore->get_module($this->module_id);
					
					if (!is_loaded($module->class_name)) {
						$path   = explode('/', $module->class_path);

						unset($path[count($path) - 1]);
						load_class($module->class_name, 'controllers/' . implode('/', $path));
					}

					if (method_exists($module->class_name, $table)) {						
						$options = call_user_func(array($module->class_name, $table));
					} else {						
						$options = $this->{$table}();
					}
				} else{
					if( !empty( $group_by ) ) $table .= " order by {$group_by}";
					$options = $this->db->query( str_replace('{dbprefix}', $this->db->dbprefix, $table) );
				}
				
				if( $this->db->_error_message() == "" )
				{
					if( strpos($name_column, ',') ) $name_column = explode(',', $name_column);

					$values = array();
					$values = explode(',', $value);
					$str .= '<input type="hidden" name="'. $name.'" id="'.$name.'" value="'.$value.'"/>';
					$str .= '<select ' . $tabindex . ' id="multiselect-'. $name.'" name="multiselect-'. $name .'" multiple="multiple">';
					$current_optgroup = "";
					foreach($options->result() as $row)
					{
						if( !empty( $group_by ) ){
							if( $current_optgroup != $row->$group_by ){
								if( empty( $current_optgroup ) )
									$prev_optgroup =  $row->$group_by;
								else
									$prev_optgroup = $current_optgroup;
								$current_optgroup = $row->$group_by;
								$str .= '<optgroup label="'. $current_optgroup .'">';
							}
						}
						
						$str .= '<option value="'.$row->$id_column.'" '.( in_array( $row->$id_column, $values ) ? 'selected' : "").'>';
						if( is_array($name_column) )
						{
							$temp = array();
							foreach($name_column as $column)
							{
								$temp[] = $row->$column;
							}
							$str .= implode(' ', $temp);
							unset($temp);
						}
						else{
							$str .= $row->$name_column;
						}

						$str .= '</option>';
						
						if( !empty( $group_by ) ){
							if( $prev_optgroup != $current_optgroup ){
								$str .= '</optgroup>';
							}
						}
					
					}
					$str .= '</select>';
				}
				else{
					$str .= $this->db->_error_message();
				}
			}
			else{
				$str .= '<span class="red">Undefined multiselect.</span>';
			}
		}
		else{
			$str .= $this->db->_error_message();
		}
		$str .= '</div><br clear="left">';
		return $str;
	}

	function multiple_upload( $field_id = 0, $name = "", $value = "" )
	{
		$str = '<div class="text-input-wrap">
			<div id="error-'.$name.'"></div>
			<div id="'.$name.'-upload-container">';
		if( $value != "" )
		{
			$this->db->order_by('upload_id');
			$this->db->where('upload_id IN ('. $value .')');
			$files = $this->db->get('file_upload');
			if( $this->db->_error_message() != "" )
			{
				$str .= $this->db->_error_message();
			}
			else{
				foreach($files->result() as $file)
				{
					$path_info = pathinfo(base_url() . $file->upload_path);
					if( in_array( $path_info['extension'], array('jpeg', 'jpg', 'JPEG', 'JPG', 'gif', 'GIF', 'png', 'PNG', 'bmp', 'BMP') ) )
					{
						$str .= '<div class="nomargin image-wrap">
									<img id="file-'. $name .'-'. $file->upload_id .'" class="enlarge-image" img_target="'.base_url() . $file->upload_path .'" src="'.base_url() . $file->upload_path .'" width="100px">
									<div class="image-delete nomargin multi" field="'.$name.'" upload_id="'. $file->upload_id .'"></div>
								</div>';
					}
					else{
						$str .= '<div class="nomargin image-wrap">
									<a id="file-'. $name .'-'. $file->upload_id .'" href="'.base_url() . $file->upload_path .'" target="_blank"><img src="'. base_url() .$this->userinfo['theme'].'/images/file-icon-md.png"></a>
									<div class="image-delete nomargin multi" field="'.$name.'" upload_id="'. $file->upload_id .'"></div>
								</div>';
					}
				}
			}
		}

		$str .= '
			</div>
			<div class="clear"></div>
			<input id="'.$name.'" name="'.$name.'" type="hidden" value="'. $value .'"/>
			<input id="uploadify-'.$name.'" name="uploadify-'.$name.'" type="file" />
		</div>';
		return $str;
	}
}
?>