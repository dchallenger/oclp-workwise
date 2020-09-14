<?php
require_once (APPPATH . 'models/uitype_base.php');

class uitype_listview extends Uitype_base
{
	function __construct()
	{
		parent::__construct();
	}
	
	function fieldValue( $field = array() )
	{
		switch($field['uitype_id']){
			case 2: //textarea
				return nl2br($field['value']);
				break;
			case 4: //yes no
				return ($field['value'] == 1 ? "Yes" : "No");
				break;
			case 11: // single image/logo
				switch($this->module){
					case 'company':
						$path = base_url().COMPANY_LOGODIR;
						break;	
				}
				return '
					<a class="enlarge-image" href="javascript:void(0)" img_target="'.$path.'fullsize/'. $field['value'] .'">
						<img src="'.$path.'fullsize/'. $field['value'] .'" width="25%" />
					</a>';
				break;
			case 12: //column left right top
				if( $field['value'] == 'left' ){
					return 'Left';
				}else  if( $field['value'] == 'right' ){
					return 'Right';
				}else if( $field['value'] == 'top' ){
					return 'Top';
				}else if( $field['value'] == 'center' ){
					return 'Center';
				}
				break;
			case 17: // Two/Single Column Layout
				return ($field['value'] == 1 ? "Single Column" : "Two Column");
				break;
			case 19: // Days Multi-select
				if($field['value'] == "")
				{
					return "";
				}
				else{
					$days_array = unserialize($field['value']);
					$str = array();
					foreach($days_array as $int)
					{
						$str[] = int_to_day($int, 'full');
					}
					return implode(', ', $str);
				}
				break;
			case 21: // Multi-select checkbox
				if($field['value'] == "")
				{
					return "";
				}
				else{
					$multiselect = $this->db->get_where('field_multiselect', array('field_id' => $field['field_id']));
					if( $this->db->_error_message() == "" )
					{
						if($multiselect->num_rows() > 0)
						{
							$str = "";
							$multiselect = $multiselect->row_array();
							$table = $multiselect['table'];
							$id_column = $multiselect['id_column'];
							$name_column = $multiselect['name_column'];
							$type = $multiselect['type'];
							$group_by = $multiselect['optgroup_column'];
							
							if( $type == "Table" ){
								$this->db->select( $id_column .', '. $name_column );
								if( $table != 'month' && $table != 'day' && $table != 'time_24hr_format') $this->db->order_by($name_column);
								$this->db->where('deleted', 0);
								$this->db->where($id_column.' IN ('.$field['value'].')');
								$options = $this->db->get( $table );
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
								$a_values = explode(',', $field['value']);
								$o_options = $options->result();
								
								foreach ($o_options as $index => $row) {
									if (!in_array($row->{$id_column}, $a_values)) {
										unset($o_options[$index]);
									}
								}
								$options = new DummyResultCollection($o_options);
							} else{
								if( strpos( strtoupper( $table ), 'WHERE') ){
									$table .= ' AND '.$id_column . ' IN ('. $field['value'] .')';
								}
								else{
									$table .= ' WHERE '.$id_column . ' IN ('. $field['value'] .')';
								} 
								
								$options = $this->db->query( str_replace('{dbprefix}', $this->db->dbprefix, $table) );
							}
							
							if( $this->db->_error_message() == "" )
							{
								if (strpos($name_column, ',')) $name_column = explode(',', $name_column);
								$str = "";
								$temp = array();
								foreach($options->result() as $row)
								{
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
							}
							else{
								$str = $this->db->_error_message();
							}
						}
						else{
							$str = '<span class="red">Undefined multiselect.</span>';
						}
					}
					else{
						$str = $this->db->_error_essage();	
					}
					return $str;
				}
				break;
			case 24; //date from date to
            	if($field['value'] != "")
				{
					$dates = explode( ' to ', $field['value'] );
					if(!empty($dates) && !is_null($dates) && $dates != '1970-01-01 08:00:00' && $dates != '0000-00-00 00:00:00') {
						return date($this->config->item('display_date_format'), strtotime($dates[0])).' to '.date($this->config->item('display_date_format'), strtotime($dates[1]));
					}
				}else{
					return "";	
				}
            	break;	
            case 5: 
					if(!empty($field['value']) && !is_null($field['value']) && $field['value'] != '0000-00-00' && $field['value'] != '0000-00-00 00:00:00') {
          	return date($this->config->item('display_date_format'), strtotime($field['value']));
					}
					else{
						return "";
					}
					break;
			case 27: //yes no
				return !empty($field['value']) ? ucfirst($field['value']) : "";
				break;
            case 32: //datetime
                    if(!empty($field['value']) && !is_null($field['value']) && $field['value'] != '1970-01-01 08:00:00' && $field['value'] != '0000-00-00 00:00:00') {
						return date($this->config->item('display_datetime_format'), strtotime($field['value']));
					}
					else{
						return "";
					}
					break;
            case 33:
                    return date($this->config->item('display_time_format'), strtotime($field['value']));                            
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
             case 36: //Radio button set
                $this->db->where('field_id', $field['field_id']);

                $result = $this->db->get('field_options')->row();

                $options = explode(',', $result->options);

                return $options[$field['value'] - 1];             	
             	break;
            case 40: 
            	$dates = explode( ' to ', $field['value'] );
            	return date($this->config->item('display_datetime_format'), strtotime($dates[0])).' to '.date($this->config->item('display_datetime_format'), strtotime($dates[1]));
            	break;
			default:
				return $field['value'];
		}
	} //end function showFieldDetail
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