<?
require_once (APPPATH . 'models/uitype_base.php');

class uitype_detail extends Uitype_base {

    function __construct() {
        parent::__construct();
    }

    function showFieldDetail($field = array()) {
        if ($field['visible'] == 1) :
            if (in_array($field['uitype_id'], array(1, 2, 3, 4, 5, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 30, 31, 32, 33,34,36,37,38,39,40,41))) {
                $display = display_field( 'detail', $field['display'] ); ?>    
                <div class="form-item view <?= ($field['sequence'] % 2 == 0 ? 'even' : 'odd') ?> <?php echo $display ? '' : 'hidden'?>">
                    <label for="<?= $field['fieldname'] ?>" class="label-desc view gray"><?= $field['fieldlabel'] ?>:</label>
                    <div class="text-input-wrap">
                        <?php echo $this->getFieldValue($field); ?>
                    </div>		
                </div><?php
            } else if (in_array($field['uitype_id'], array(6, 7, 8, 9))) {
                if ($field['uitype_id'] == 6 || $field['uitype_id'] == 8) {
                            ?>
                    <div class="form-item view <?= ($field['sequence'] % 2 == 0 ? 'even' : 'odd') ?>">
                        <label for="<?= $field['fieldname'] ?>" class="label-desc view gray"><?= $field['fieldlabel'] ?>:</label>
                        <div class="text-input-wrap"><?
                    if (CLIENT_DIR == 'firstbalfour'){
                        if ($field['uitype_id'] != 6){
                            echo $field['value'];
                        }
                    }
                    else{
                        echo $field['value'];
                    }
                } else {
                    echo $field['value'];
                            ?>
                        </div>		
                    </div><?
                }
            } /*else if ($field['uitype_id'] == 16) {
                echo stripslashes($field['value']);
            }*/
        endif;
                ?>	
        <?php
    }

//end function showFieldDetail

    /**
     * Returns value of a field.
     * Separated this so it can be used by other functions aside from showFieldDetail()     
     * 
     * @param array $field
     * @return string 
     */
    function getFieldValue($field) {

        switch ($field['uitype_id']) {
            case 1: //textfield
                return $field['value'] != "" ? $field['value'] : '&nbsp;';
                break;
            case 2; //textarea
                return $field['value'] != "" ? nl2br($field['value']) : '&nbsp;';
                break;
            case 3; //simple select
                if ($field['value'] != "") {
                    //get detail of dropdown
                    $picklist = $this->db->get_where('picklist', array('field_id' => $field['field_id']));
                    if ($picklist->num_rows()) {
                        $picklist = $picklist->row_array();
                        $id_column = $picklist['picklist_name'] . '_id';
                        $name_column = $picklist['picklist_name'];
                        $picklist_table = $picklist['picklist_table'];
                        $picklist_type = $picklist['picklist_type'];
                        //get actual values from table 
                        if ($picklist_type == "Table") {
                            $this->db->select($id_column . ', ' . $name_column);
                            $this->db->from($picklist_table);
                            $this->db->order_by($name_column);
                            $this->db->where(array('deleted' => 0));
                            $picklistvalues = $this->db->get();
                        } else {
                            $picklistvalues = $this->db->query(str_replace('{dbprefix}', $this->db->dbprefix, $picklist_table));
                        }

                        if ($this->db->_error_message() == "") {
                            $picklistvalues = $picklistvalues->result_array();
														$val = '';
                            foreach ($picklistvalues as $index => $option) {
                                if ($field['value'] == $option[$id_column])
                                    $val .= $option[$name_column];
                            }
                            
                            return $val;
                        }
                        else {
                            return $this->db->_error_message();
                        }
                    }
                }
                break;
            case 4;
            case 30; //yes no
                return ($field['value'] == 1 ? "Yes" : "No");            
                break;
            case 5; //date
                if ($field['value'] == '0000-00-00') {
                    return '';
                }

                if (!empty($field['value'])) {
                    return date($this->config->item('display_date_format'), strtotime($field['value']));
                }
                break;
            case 10; //password
                return "******";
                break;
            case 11; // single image/logo
                if (empty($field['value'])) {
                    return '<img src="' . base_url() . $this->userinfo['theme'] . '/images/no-photo.jpg" width="100px" />';
                } else {
                    return '<a class="enlarge-image" href="javascript:void(0)" img_target="' . base_url() . $field['value'] . '">
										<img src="' . base_url() . $field['value'] . '" width="25%" />
									</a>';
                }
                break;
            case 20; // multiple image/logo
                if (empty($field['value'])) {
                    return '<img src="' . base_url() . $this->userinfo['theme'] . '/images/no-photo.jpg" width="100px" />';
                } else {
                    $this->db->order_by('upload_id');
                    $this->db->where('upload_id IN (' . $field['value'] . ')');
                    $files = $this->db->get('file_upload');
                    if ($this->db->_error_message() != "") {
                        return $this->db->_error_message();
                    } else {
                        $val = '';
                        foreach ($files->result() as $file) {
                            $path_info = pathinfo(base_url() . $file->upload_path);                            
                            if (in_array($path_info['extension'], array('jpeg', 'jpg', 'JPEG', 'JPG', 'gif', 'GIF', 'png', 'PNG', 'bmp', 'BMP'))) {
                                $val .= '<a class="enlarge-image" href="javascript:void(0)" img_target="' . base_url() . $file->upload_path . '">
													<img src="' . base_url() . $file->upload_path . '" width="25%" />
												</a>';
                            } else {
                                $val .= '<a href="' . base_url() . $file->upload_path . '" target="_blank">
													<img src="' . base_url() . $this->userinfo['theme'] . '/images/file-icon-md.png" />
												</a>';
                            }
                        }
                        
                        return $val;
                    }
                }
                break;
            case 12; //column left right no
                if ($field['value'] == 'left') {
                    return 'Left';
                } else if ($field['value'] == 'right') {
                    return 'Right';
                } else if ($field['value'] == 'top') {
                    return 'Top';
                } else if ($field['value'] == 'center') {
                    return 'Center';
                }
                break;
            case 13: // FMLink
                if ( empty( $field['value'] ) && ($this->module == "module" || $this->module == "role") ) {
                    return "Top Level";
                }
								else {
                    //get related module field
                    $this->db->select('a.module_id, a.column, field.table, module.key_field, module.class_path');
                    $this->db->from('field_module_link a');
                    $this->db->join('module', 'module.module_id = a.module_id', 'left');
                    $this->db->join('field', 'field.module_id = a.module_id', 'left');
                    $this->db->where(array('a.field_id' => $field['field_id']));
                    $module = $this->db->get();
                    if ($this->db->_error_message() == "") {
                        if ($module->num_rows() > 0) {
                            $module = $module->row_array();
                            $module_id = $module['module_id'];
                            $link = $module['class_path'];
                            $column = $module['column'];
														$table = $module['table'];
                            $key_field = $module['key_field'];
                            $this->db->select($column);
                            $valuename = $this->db->get_where($table, array($key_field => $field['value']));
                           if ($this->db->_error_message() == "") {
                                if ($valuename->num_rows() > 0) {
                                    $valuename = $valuename->row_array();
                                    if (strpos($column, ',')) {
                                        $temp_val = array();
                                        $column_lists = explode(',', $column);
                                        foreach ($column_lists as $col_index => $column) {
                                            $temp_val[] = $valuename[$column];
                                        }
                                        $name_val = implode(' ', $temp_val);
                                    } else {
                                        if(sizeof(explode(' AS ', $column)) > 1 ){
																					$as_part = explode(' AS ', $column);
																					$column = strtolower( trim( $as_part[1] ) );
																				}
																				else if(sizeof(explode(' as ', $column)) > 1 ){
																					$as_part = explode(' as ', $column);
																					$column = strtolower( trim( $as_part[1] ) );
																				}
																				
																				$name_val = $valuename[$column];
                                    }
                                    return '<a href="javascript:void(0)" onclick="seeDetail(\'' . $field['value'] . '\', \'' . $link . '\')">' . $name_val . '</a>';
                                }
                            } else {
                                return $this->db->_error_message();
                            }
                        }
                    } else {
                        return $this->db->_error_message();
                    }
                }
                break;
            case 14: //fieldgroup value
                $fg = $this->db->get_where('fieldgroup', array('fieldgroup_id' => $field['value']));
                if ($this->db->_error_message() == "") {
                    if ($fg->num_rows() > 0 && $fg->num_rows() == 1) {
                        $fg = $fg->row_array();
                        return $fg['fieldgroup_label'];
                    } else {
                        return "Fieldgroup width fieldgroup_id " . $field['value'] . " was not found.";
                    }
                } else {
                    return $this->db->_error_message();
                }
                break;
            case 17; // Two/Single Column Layout
                return ($field['value'] == 1 ? "Single Column" : "Two Column");
                break;
            case 21:
                return $this->multiselect($field['field_id'], $field['value']);
                break;
            case 27: //yes no
                return (!empty($field['value']) ? ucfirst($field['value']) : "");
                break;
            case 32: // Datetime
                if (!is_null($field['value']) && $field['value'] != '0000-00-00 00:00:00') {
                    return date($this->config->item('display_datetime_format'), strtotime($field['value']));
                } else {
                    return '';
                }
                break;
            case 33:                
                return date($this->config->item('display_time_format'), strtotime($field['value']));
                break;
            case 36:
                $this->db->where('field_id', $field['field_id']);

                $result = $this->db->get('field_options')->row();

                $options = explode(',', $result->options);

                return $options[$field['value'] - 1];
                break;
            case 37: // minute seconds
                if (!is_null($field['value']) && $field['value'] != '00:00:00') {
                    return date($this->config->item('display_mmss_format'), strtotime($field['value']));
                } else {
                    return '';
                }
                break;                
            case 39:
                $value = explode(',', $field['value']);

                $this->db->where('field_id', $field['field_id']);
                $this->db->where('deleted', 0);
                $this->db->limit(1);

                $result = $this->db->get('field_autocomplete');             

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
                        $results = $this->db->get($row->table)->result_array();                     
                    }

                    $options = array();
                    foreach ($results as $option) {
                        $labels = explode(',', $row->label);

                        $label = '';
                        foreach ($labels as $l) {
                            $label[] = $option[$l];
                        }                           
                                 
                        if (in_array($option[$row->value], $value)) {
                            $options[$option[$row->value]] = implode(' ', $label);
                        }
                    }

                    return implode(', ', $options);
                }
                return '';
                break;
            case 41: // hour minute seconds
                if (!is_null($field['value']) && $field['value'] != '00:00:00') {
                    return date($this->config->item('display_hhmmss_format'), strtotime($field['value']));
                } else {
                    return '';
                }
                break;                
            default:
                return $field['value'] != "" ? $field['value'] : '&nbsp;';
        }
    }

    function multiselect($field_id = 0, $value = 0) {
        if ($value == "") {
            return "";
        } else {
            $multiselect = $this->db->get_where('field_multiselect', array('field_id' => $field_id));
            if ($this->db->_error_message() == "") {
                if ($multiselect->num_rows() > 0) {
                    $multiselect = $multiselect->row_array();
                    $table = $multiselect['table'];
                    $id_column = $multiselect['id_column'];
                    $name_column = $multiselect['name_column'];
										$type = $multiselect['type'];
										$group_by = $multiselect['optgroup_column'];

                    if( $type == "Table" ){
											$this->db->select($id_column . ', ' . $name_column);
											$this->db->where('deleted', '0');
											$this->db->where($id_column . ' IN (' . $value . ')');
											if ($table != 'month' && $table != 'day' && $table != 'time_24hr_format') $this->db->order_by($name_column);
											$options = $this->db->get($table);
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

                                            // Filter again, we do it on this layer because we cannot use the query
                                            // "$this->db->where_in($id_column, explode(',',$field['value']));"
                                            // since functions may have different queries.
                                            $a_values = explode(',', $value);
                                            $o_options = $options->result();                                           
                                            foreach ($o_options as $index => $row) {
                                                if (!in_array($row->{$id_column}, $a_values)) {
                                                    unset($o_options[$index]);
                                                }
                                            }                                                                                   
                                            $options = new DummyResultCollection($o_options);                                          
										} else {
											if( strpos( strtoupper( $table ), 'WHERE') ){
												$table .= ' AND '.$id_column . ' IN ('. $value .')';
											}
											else{
												$table .= ' WHERE '.$id_column . ' IN ('. $value .')';
											} 
											
											$options = $this->db->query( str_replace('{dbprefix}', $this->db->dbprefix, $table) );
										}
										if ($this->db->_error_message() == "") {
                        if (strpos($name_column, ',')) $name_column = explode(',', $name_column);

                        $str = "";
                        $temp = array();
                        foreach ($options->result() as $row) {
                            if (is_array($name_column)) {
                                $temp_val = array();
                                foreach ($name_column as $column) {
                                    $temp_val[] = $row->$column;
                                }
                                $temp[] = implode(' ', $temp_val);
                                unset($temp_val);
                            } else {
                                $temp[] = $row->$name_column;
                            }
                        }
                        $str .= implode(', ', $temp);
                    } else {
                        $str = $this->db->_error_message();
                    }
                } else {
                    $str = '<span class="red">Undefined multiselect.</span>';
                }
            } else {
                $str = $this->db->_error_essage();
            }
            return $str;
        }
    }
}

class DummyResultCollection
{
    private $_result;

    public function __construct($records)
    {
        foreach ($records as $record) {
            $this->_result[] = (object) $record;
        }
    }

    public function result()
    {
        return $this->_result;
    }
}
?>