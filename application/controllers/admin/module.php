<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Module extends MY_Controller
{
	function __construct(){
     parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Installed Modules';
		$this->listview_description = 'This module lists all defined module(s).';
		$this->jqgrid_title = "Installed Modules List";
		$this->detailview_title = 'Module Info';
		$this->detailview_description = 'This page shows detailed information about a particular module';
		$this->editview_title = 'Module Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about modules';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js	
		$data['content'] = 'listview';
		
		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}
		
		//set default columnlist
		$this->_set_listview_query();
		
		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();
		
		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";
		
		//load variables to env
		$this->load->vars( $data );
		
		//load the final view
		//load header
		$this->load->view( $this->userinfo['rtheme'].'/template/header' );
		$this->load->view( $this->userinfo['rtheme'].'/template/header-nav' );
		
		//load page content
		$this->load->view( $this->userinfo['rtheme'].'/template/page-content' );
		
		//load footer
		$this->load->view( $this->userinfo['rtheme'].'/template/footer' );
    }
	
	function detail()
	{	
		parent::detail();
		
		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'detailview';
		
		//other views to load
		$data['views'] = array();
		
		//load variables to env
		$this->load->vars( $data );
		
		//load the final view
		//load header
		$this->load->view( $this->userinfo['rtheme'].'/template/header' );
		$this->load->view( $this->userinfo['rtheme'].'/template/header-nav' );
		
		//load page content
		$this->load->view( $this->userinfo['rtheme'].'/template/page-content' );
		
		//load footer
		$this->load->view( $this->userinfo['rtheme'].'/template/footer' );
	}
	
	function edit()
	{
		if($this->user_access[$this->module_id]['edit'] == 1)
		{
			parent::edit();
			
			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}
			$data['content'] = 'editview';
			
			//other views to load
			$data['views'] = array($this->module_link.'/module_fieldgroups_and_fields', $this->module_link.'/module_listview_edit');
			$data['views_outside_record_form'] = array();
			
			//load variables to env
			$this->load->vars( $data );
			
			//load the final view
			//load header
			$this->load->view( $this->userinfo['rtheme'].'/template/header' );
			$this->load->view( $this->userinfo['rtheme'].'/template/header-nav' );
			
			//load page content
			$this->load->view( $this->userinfo['rtheme'].'/template/page-content' );
			
			//load footer
			$this->load->view( $this->userinfo['rtheme'].'/template/footer' );
		}
		else{
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}		
	
	function ajax_save()
	{	
		parent::ajax_save();
		
		//additional module save routine here
		
		//additional module save routine here
		$this->_delete_nav_and_access();
		$this->_set_admin_access();			                                
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}
	// END - default module functions
	
	// START custom module funtions
	
	/**
	 * Reset Access of admin
	 */
	function _set_admin_access()
	{
		$module_access = array();
		
		//get list of modules
		$this->db->order_by('parent_id, sequence');
		$this->db->select('module_id, short_name');
		$modulelist = $this->db->get('module')->result_array();
		
		// get list of module actions
		$this->db->order_by('id');
		$actionlist = $this->db->get('module_action')->result_array();
		foreach($modulelist as $index => $module )
		{
			$module_access[$module['module_id']] = array();
			foreach($actionlist as $index => $action)
			{
				$module_access[$module['module_id']][$action['action']] = 1;
			}
		}
		
		$module_access = serialize($module_access);
		$this->db->where('profile_id', '1');
		$this->db->update('profile', array('module_access' => $module_access));
	}

	function _delete_nav_and_access()
	{
		//delete all Access files to refresh access 
		$this->load->helper('file');
		$app_directories =  $this->hdicore->_get_config('app_directories');
		$user_nav_files = get_dir_file_info($app_directories['user_settings_dir']);
		foreach($user_nav_files as $filename => $file_detail)
		{
			unlink($app_directories['user_settings_dir'].$filename);
		}
	}
	
	function get_fieldgroup()
	{
		if(IS_AJAX){
			if($this->user_access[$this->module_id]['edit'] == 1){
				$response->msg = "";
				//get field groups
				$module_id = $this->input->post('module_id');
				$this->db->order_by('sequence');
				$fieldgroups = $this->db->get_where('fieldgroup', array("module_id" => $module_id, 'deleted' => 0));
				$str = "";
				if( $fieldgroups->num_rows() > 0 ) { 
					$fieldgroups = $fieldgroups->result_array();
					foreach($fieldgroups as $fgroup_index => $fg_detail){
						$str .= '<tr id="fg-'.$fg_detail['fieldgroup_id'].'"><td colspan="3" style="background: none; padding-left: 0;padding-right:0;"><div class="box">';
						$str .= '<div class="align-left" style="width: 19%;margin-right:1%">'.$fg_detail['fieldgroup_label'].'</div>';
						$str .= '<div class="align-left" style="width: 59%;margin-right:1%">';
						$qry = "select a.field_id, a.sequence, a.uitype_id, a.fieldlabel,
						IFNULL(b.picklist_id, -1) as picklist_id,
						IFNULL(c.fm_link_id, -1) as fm_link_id,
						IFNULL(d.multiselect_id, -1) as multiselect_id,
						IFNULL(e.field_option_id, -1) as field_option_id,
						IFNULL(f.field_autocomplete_id, -1) as field_autocomplete_id
						FROM {$this->db->dbprefix}field a 
						LEFT  JOIN {$this->db->dbprefix}picklist b on b.field_id = a.field_id AND b.deleted = '0'
						LEFT  JOIN {$this->db->dbprefix}field_module_link c on c.field_id = a.field_id
						LEFT  JOIN {$this->db->dbprefix}field_multiselect d on d.field_id = a.field_id
						LEFT  JOIN {$this->db->dbprefix}field_options e on e.field_id = a.field_id
						LEFT  JOIN {$this->db->dbprefix}field_autocomplete f on f.field_id = a.field_id
						WHERE a.fieldgroup_id = '{$fg_detail['fieldgroup_id']}' and a.deleted = '0'		
						ORDER BY sequence";
						$fields = $this->db->query($qry);
						$field_array_left = array();
						$field_array_right = array();
						if($fields->num_rows() > 0){
							$fields = $fields->result_array();
							foreach($fields as $field){
								$button_set = '<div class="icon-group align-right">';
								$button_set .= ( $field['uitype_id'] == 3 ?  '<a href="javascript:void(0)" onclick="editPicklist(\''. $field['picklist_id'] .'\', \''. $field['field_id'] .'\')" class="icon-button icon-16-picklist align-right"></a>' : "");
								$button_set .= ($field['uitype_id'] == 13 ?  '<a href="javascript:void(0)" onclick="editFMLink(\''. $field['fm_link_id'] .'\', \''. $field['field_id'] .'\')" class="icon-button icon-16-fmlink align-right"></a>' : "");
								$button_set .= ($field['uitype_id'] == 21 ?  '<a href="javascript:void(0)" onclick="editMultiselect(\''. $field['multiselect_id'] .'\', \''. $field['field_id'] .'\')" class="icon-button icon-16-multiselect align-right"></a>' : "");
								/** Option set **/
								$button_set .= ($field['uitype_id'] == 36 ?  '<a href="javascript:void(0)" onclick="editOptionSet(\''. $field['field_option_id'] .'\', \''. $field['field_id'] .'\')" class="icon-button icon-16-picklist align-right"></a>' : "");
								/** Autocomplete (multiple) **/
								$button_set .= ($field['uitype_id'] == 39 ?  '<a href="javascript:void(0)" onclick="editAutocomplete(\''. $field['field_autocomplete_id'] .'\', \''. $field['field_id'] .'\')" class="icon-button icon-16-default align-right"></a>' : "");

								$button_set .= '<a href="javascript:void(0)" onclick="editField(\''. $fg_detail['fieldgroup_id'] .'\', \''. $field['field_id'] .'\')" class="icon-button icon-16-edit align-right"></a><a href="javascript:void(0)" onclick="deleteField(\''. $field['field_id'] .'\')" class="icon-button icon-16-delete align-right"></a></div>';
								
								if( $field['sequence'] % 2 == 0 ){
									$field_array_right[] = '<div class="box field_label" style="padding: 5px;border:1px solid #ddd; background: #fff; margin-bottom: 5px; margin-right: 5px" id="f-'.$field['field_id'].'">'.$field['fieldlabel'].$button_set.'</div>';
								}else{
									$field_array_left[] = '<div class="box field_label" style="padding: 5px;border:1px solid #ddd; background: #fff; margin-bottom: 5px; margin-right: 5px" id="f-'.$field['field_id'].'">'.$field['fieldlabel'].$button_set.'</div>';
								}
							}
						}
						$str .= '<div fg_id="'. $fg_detail['fieldgroup_id'] .'" class="align-left column-left connect" style="width:50%; padding-bottom:10px">'.implode("", $field_array_left).'</div>';
						$str .= '<div fg_id="'. $fg_detail['fieldgroup_id'] .'" class="align-right column-right connect" style="width:50%; padding-bottom:10px">'.implode("", $field_array_right).'</div>';
						$str .= '<div class="clear"></div>';
						
						$str .=	'</div>';
						$str .=	'<div class="align-left text-center" style="width: 19%;margin-right:1%">
						<div class="icon-group nowrap"><a tooltip="Toggle Visibility" href="javascript:void(0)" class="icon-button '. ($fg_detail['visible'] == 1 ? 'icon-16-active' : 'icon-16-xgreen-orb') .'" onclick="toggleVisibility($(this), \''.$fg_detail['fieldgroup_id'].'\')"></a><a tooltip="Add new field" href="javascript:void(0)" class="icon-button icon-16-add" onclick="editField(\''.$fg_detail['fieldgroup_id'].'\', \'-1\')"></a><a tooltip="Edit" href="javascript:void(0)" class="icon-button icon-16-edit" onclick="editFieldGroup(\''.$fg_detail['fieldgroup_id'].'\', \''.$fg_detail['sequence'].'\')"></a><a tooltip="Delete" href="javascript:void(0)" class="icon-button icon-16-delete" onclick="deleteFieldGroup(\''.$fg_detail['fieldgroup_id'].'\')"></a></div>';
						$str .= '</div></td></tr>';
					}
					$response->sortable = true;
				}
				else{
					$str .= '<tr class="no-fg">
						<td colspan="3" align="center">
							<div class="">
								There are no defined Field Group for this module.
							</div>
						</td>
					</tr>';
					$response->sortable = false;
				}
				$response->fieldgroup = $str;
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}	
	}
	
	function get_listview()
	{
		if(IS_AJAX){
			if($this->user_access[$this->module_id]['edit'] == 1){
				$response->msg = "";
				//get listview fields
				$module_id = $this->input->post('module_id');
				$listviews = $this->db->get_where('listview', array("module_id" => $module_id, 'deleted' => 0));
				$str = "";
				if( $listviews->num_rows() > 0 ) { 
					$listviews = $listviews->result_array();
					foreach($listviews as $lv_index => $lv_detail){
						$str .= '<tr class="'. ($lv_index % 2 == 1 ? 'odd' : 'even') .'" id="lv-'. $lv_detail['listview_id'] .'">';
						$str .= '<td style="vertical-align:top;">'. $lv_detail['listview_name'] .'</td>';
						$str .= '<td class="sort-listview" lv_id="'.$lv_detail['listview_id'].'">';
						$this->db->select('listviewcolumn_list.field_id, fieldlabel');
						$this->db->join('field', 'field.field_id = listviewcolumn_list.field_id', 'left');
						$this->db->order_by('listviewcolumn_list.sequence');
						$fields = $this->db->get_where('listviewcolumn_list', array("listview_id" => $lv_detail['listview_id'], "listviewcolumn_list.deleted" => 0));
						if($fields->num_rows() > 0){
							$fields = $fields->result_array();
							foreach($fields as $field){
								$str .= '<div class="box align-left" style="padding: 5px; border:1px solid #ddd; background: #fff; margin-bottom: 5px; margin-right: 5px" id="lvf-'.$field['field_id'].$lv_detail['listview_id'].'">'.$field['fieldlabel'].'
								<div class="icon-group nowrap  align-right" style="margin-left: 5px"><a href="javascript:void(0)" onclick="editListviewColumn(\''. $lv_detail['listview_id'] .'\', \''. $field['field_id'] .'\')" class="icon-button icon-16-edit"></a><a href="javascript:void(0)" onclick="deleteFieldFromListview(\''. $lv_detail['listview_id'] .'\', \''. $field['field_id'] .'\')" class="icon-button icon-16-delete"></a></div>
								</div>';
							}
							$str .= '<div class="clear"></div>';
						}
						$str .='</td>';
						$str .= '<td align="center" style="vertical-align:top;">
						<div class="icon-group nowrap"><a tooltip="Toggle Default" href="javascript:void(0)" class="icon-button '. ($lv_detail['default'] == 1 ? 'icon-16-active' : 'icon-16-xgreen-orb') .'" onclick="toggleDefaultListview(\''.$module_id.'\', \''.$lv_detail['listview_id'].'\')"></a><a tooltip="Add new field" href="javascript:void(0)" class="icon-button icon-16-add" onclick="addColumn(\''.$lv_detail['listview_id'].'\')"></a><a tooltip="Edit" href="javascript:void(0)" class="icon-button icon-16-edit" onclick="editListview(\''.$lv_detail['listview_id'].'\')"></a><a tooltip="Delete" href="javascript:void(0)" class="icon-button icon-16-delete" onclick="deleteListview(\''.$lv_detail['listview_id'].'\')"></a></div>
						</td>';
						$str .=	'</tr>';
					}
					$response->sortable = true;
				}
				else{
					$str .= '<tr class="no-lv">
						<td colspan="3" align="center">
							<div class="">
								There are no defined List View for this module.
							</div>
						</td>
					</tr>';
					$response->sortable = false;
				}
				$response->listview = $str;
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}	
	}
	
	function get_lv_avail_field()
	{
		if( IS_AJAX ){
			$response->msg = "";
			if($this->user_access[$this->module_id]['edit'] == 1){
				if( $this->input->post('listview_id') ){
					$qry = "SELECT c.field_id, c.fieldlabel, d.fieldgroup_label
					FROM {$this->db->dbprefix}listview a
					LEFT JOIN {$this->db->dbprefix}field c ON c.module_id = a.module_id
					LEFT JOIN {$this->db->dbprefix}fieldgroup d ON c.fieldgroup_id = d.fieldgroup_id
					WHERE a.listview_id = '{$this->input->post('listview_id')}'
					AND c.field_id NOT IN (SELECT field_id FROM {$this->db->dbprefix}listviewcolumn_list WHERE deleted = 0 AND listview_id = '{$this->input->post('listview_id')}' ) AND d.deleted = 0 AND c.deleted = 0 
					ORDER BY d.sequence, c.sequence";
					$fields = $this->db->query($qry);
					if( $fields->num_rows() > 0 ){
						$fields = $fields->result_array();
						$previous_fg = $fields[0]['fieldgroup_label'];
						$str = "";
						$str .= '<div class="fg-label"><h3 class="form-head">'.$previous_fg.'</h3></div>';
						$str .= '<div class="field-div">';
						$ctr = 0;
						$array_left = array();
						$array_right = array();
						foreach($fields as $index => $field){
							$current_fg = $field['fieldgroup_label'];
							if($current_fg != $previous_fg ){
								$str .= '<div class="align-left" style="width:45%">'.implode("", $array_left).'</div>';
								$str .= '<div class="align-right" style="width:45%">'.implode("", $array_right).'</div>';
								$array_left = array();
								$array_right = array();
								$str .= '<div class="clear"></div></div>';
								$str .= '<div class="spacer"></div>';
								$str .= '<div class="fg-label"><h3 class="form-head">'.$current_fg.'</h3></div>';
								$str .= '<div class="field-div">';
								$previous_fg = $current_fg;
								$ctr = 0;
							}
							$strx= "";
							$strx .= '<div id="alvf-'.$field['field_id'].'" class="box" style="padding: 5px; border:1px solid #ddd; background: #fff; margin-bottom: 5px; margin-right: 5px;">';
							$strx .= $field['fieldlabel'];
							$strx .= ' <a href="javascript:void(0)" onclick="addToListview(\''. $this->input->post('listview_id') .'\', \''. $field['field_id'] .'\')" class="icon-button icon-16-add align-right"></a>';
							$strx .= '</div>';
							if($ctr % 2 == 1){ $array_right[] = $strx;}else{ $array_left[] = $strx; }
							$ctr++;
						
						}
						$str .= '<div class="align-left" style="width:45%">'.implode("", $array_left).'</div>';
						$str .= '<div class="align-right" style="width:45%">'.implode("", $array_right).'</div>';
						$str .= '<div class="clear"></div></div>';
						$response->avail_fields = $str;
					}else{
						$response->avail_fields = "";
						$response->msg = "There are no available fields to add to selected listview!";
						$response->msg_type = 'attention';
					}
				}
				else{
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'attention';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
	
	function add_field_to_listview()
	{
		if( IS_AJAX ){
			$response->msg = "";
			if($this->user_access[$this->module_id]['edit'] == 1){
				if( $this->input->post('listview_id') && $this->input->post('field_id') ){
					$listview_id = $this->input->post('listview_id');
					$field_id = $this->input->post('field_id');
					//check if the field has been inserted before, in this case just undelete
					$checker = $this->db->get_where('listviewcolumn_list', array('listview_id' => $listview_id, 'field_id' => $field_id));
					if($checker->num_rows() > 0){
						$this->db->where(array('listview_id' => $listview_id, 'field_id' => $field_id));
						$this->db->update('listviewcolumn_list', array('deleted' => 0));	
					}
					else{
						//get sequence
						$qry = "SELECT MAX(sequence) AS sequence FROM {$this->db->dbprefix}listviewcolumn_list WHERE listview_id = '{$listview_id}'";
						$seq = $this->db->query($qry)->result_array();
						$seq = $seq[0]['sequence']+1;
						$sequence = $this->db->get_where('listviewcolumn_list', array('listview_id' => $listview_id, 'field_id' => $field_id));
						$data = array(
							'listview_id' => $listview_id,
							'field_id' => $field_id,
							'sequence' => $seq
						);
						$this->db->insert('listviewcolumn_list', $data); 
					}
					
					$response->msg = "Field added to Listview.";
					$response->msg_type = 'success';
				}
				else{
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'attention';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
	
	function edit_listview_column()
	{
		if(IS_AJAX){
			$response->msg = "";
			if($this->user_access[$this->module_id]['edit'] == 1){
				if( $this->input->post('listview_id') && $this->input->post('field_id') ){
					$listview_id = $this->input->post('listview_id');
					$field_id = $this->input->post('field_id');
					$this->db->select('listviewcolumn_list.*, fieldlabel');
					$this->db->where(array('listview_id' => $listview_id, 'listviewcolumn_list.field_id' => $field_id ));
					$this->db->from('listviewcolumn_list');
					$this->db->join('field', 'listviewcolumn_list.field_id = field.field_id');
					$column = $this->db->get();
					if($column->num_rows() > 0 )
					{
						$column = $column->row_array();
						$response->editform = $this->load->view($this->userinfo['rtheme'].'/admin/module/edit_listview_column', $column, true);
					}
					else{
						$response->msg = "Data not found.";
						$response->msg_type = 'attention';
					}
				}
				else{
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'attention';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
	
	function save_listview_column()
	{
		if(IS_AJAX){
			$response->msg = "";
			if($this->user_access[$this->module_id]['edit'] == 1){
				$this->db->where(array('listview_id' => $this->input->post('listview_id'), 'field_id' => $this->input->post('field_id')));
				$this->db->update('listviewcolumn_list', $_POST); 
				
				$response->msg = "Listview Column setup saved.";
				$response->msg_type = 'success';
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
	
	function delete_field_from_listview()
	{
		if( IS_AJAX ){
			$response->msg = "";
			if($this->user_access[$this->module_id]['delete'] == 1)
			{
				if( $this->input->post('listview_id') && $this->input->post('field_id') ){
					$this->db->where(array('listview_id' => $this->input->post('listview_id'), 'field_id' => $this->input->post('field_id')));
					$this->db->update('listviewcolumn_list', array('deleted' => 1));
					
					$response->msg = "Field deleted from Listview.";
					$response->msg_type = 'success';
				}
				else{
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'attention';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
	
	function default_listview()
	{
		if( IS_AJAX ){
			$response->msg = "";
			if($this->user_access[$this->module_id]['edit'] == 1){
				if( $this->input->post('listview_id') && $this->input->post('module_id')){
					$this->db->where(array('module_id' => $this->input->post('module_id')));
					$this->db->update('listview', array('default' => 0));
					
					$this->db->where(array('listview_id' => $this->input->post('listview_id')));
					$this->db->update('listview', array('default' => 1));
					
					$response->msg = "Listview set as default.";
					$response->msg_type = 'success';
				}
				else{
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'attention';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
	
	function fg_toggle_visibility()
	{
		if( IS_AJAX ){
			$response->msg = "";
			if( $this->user_access[$this->module_id]['edit'] == 1 ){
				if( isset($_POST['visible']) && $this->input->post('fieldgroup_id') ){
					$visible = $this->input->post('visible');
					$fieldgroup_id = $this->input->post('fieldgroup_id');
					$this->db->where('fieldgroup_id', $fieldgroup_id);
					$this->db->update('fieldgroup', array('visible' => $visible));
					
					$response->msg = ( $_POST['visible'] == 0 ? 'Field Group visibility set to none.' : 'Field Group is now visible.');
					$response->msg_type = 'success';
				}
				else{
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'attention';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect( base_url().$this->module_link );
		}
	}
	function toggle_module_state()
	{
		if( IS_AJAX ){
			$response->msg = "";
			if( $this->user_access[$this->module_id]['edit'] == 1 ){
				if( isset($_POST['visible']) && $this->input->post('module_id') ){
					$visible = $this->input->post('visible');
					$module_id = $this->input->post('module_id');
					$this->db->where('module_id', $module_id);
					$this->db->update('module', array('inactive' => $visible));
					
					$this->_delete_nav_and_access();
					
					$response->msg = ( $_POST['visible'] == 0 ? 'Module set to active.' : 'Module set to inactive.');
					$response->msg_type = 'success';
				}
				else{
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'attention';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect( base_url().$this->module_link );
		}
	}
	
	function listview_sequence()
	{
		if( IS_AJAX ){
			$response->msg = "";
			if($this->user_access[$this->module_id]['delete'] == 1){
				if( $this->input->post('listview_id') && $this->input->post('lvf')){
					$lvf = $this->input->post('lvf');
					$listview_id = $this->input->post('listview_id');
					foreach($lvf as $sequence => $field){
						$field_id = substr( $field, 0, strlen($field) - strlen($listview_id) );
						$this->db->where(array('listview_id' => $listview_id, 'field_id' => $field_id));
						$this->db->update('listviewcolumn_list', array('sequence' => ($sequence+1)));
					}
				}
				else{
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'attention';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
	
	function fieldgroup_sequence()
	{
		if(IS_AJAX)
		{
			$response->msg = "";
			if($this->user_access[$this->module_id]['edit'] == 1)
			{
				if( $this->input->post('module_id') && $this->input->post('fg'))
				{
					$fg = $this->input->post('fg');
					foreach($fg as $sequence => $fg_id){
						$this->db->where('fieldgroup_id', $fg_id);
						$this->db->update('fieldgroup', array('sequence' => ($sequence+1)));
					}
				}
				else{
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'attention';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
	
	function field_sequence()
	{
		if( IS_AJAX ){
			$response->msg = "";
			if($this->user_access[$this->module_id]['edit'] == 1){
				if( $this->input->post('fieldgroup_id') && $this->input->post('f') &&  $this->input->post('column')){
					$field = $this->input->post('f');
					$fieldgroup_id = $this->input->post('fieldgroup_id');
					foreach($field as $sequence => $field_id){
						if( $this->input->post('column') == "left")
							$seq = ( $sequence * 2 ) + 1;
						else
							$seq = ( $sequence * 2 ) + 2;
						$this->db->where('field_id', $field_id);
						$this->db->update('field', array('fieldgroup_id' => $fieldgroup_id, 'sequence' => $seq));
					}
				}
				else{
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'attention';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
	
	function export_sqlscript(){
		if( $this->user_access[$this->module_id]['edit'] == 1 ){
			
			if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
			$record_id = $this->input->post('record_id');
			$script = "";
			
			$module = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row_array();
			unset($module[$this->key_field]); //remove the module id
			unset($module['code']); //unset key code
			
			//main module db insert
			$mod_columns = array_keys( $module );
			$script .= 'insert into '.$this->db->dbprefix.$this->module_table .'(`'.implode('`,`', $mod_columns).'`)';
			$values = array();
			foreach($mod_columns as $column){
				$values[] = '"'. $module[$column] .'"';
			}
			$script .= ' values('. implode(',', $values) .');'."\r\n";
			$script .= 'SET @module_id = LAST_INSERT_ID();'."\r\n\r\n";				
			
			//module fieldgroups
			$fieldgroup = $this->db->get_where('fieldgroup', array($this->key_field => $record_id))->result_array();
			if( sizeof( $fieldgroup ) > 0 ){
					foreach($fieldgroup as $index => $fg){
						$fg_id = $fg['fieldgroup_id'];
						unset($fg['fieldgroup_id']); //remove the field group id
						$fg_columns = array_keys( $fg );
						$script .= 'insert into '.$this->db->dbprefix.'fieldgroup' .'(`'.implode('`,`', $fg_columns).'`)';
						$values = array();
						foreach($fg_columns as $column){
							if($column == $this->key_field){
								$values[] = '@module_id';
							}
							else{
								$values[] = '"'. $fg[$column] .'"';
							}
						}
						$script .= ' values('. implode(',', $values) .');'."\r\n";
						$script .= 'SET @fg_id = LAST_INSERT_ID();'."\r\n";	
						//get the fields of field group
						$field = $this->db->get_where('field', array('fieldgroup_id' => $fg_id))->result_array();
						if( sizeof( $field ) > 0 ){
							foreach($field as $index2 => $f){
								unset($f['field_id']); //remove the field id
								$f_columns = array_keys( $f );
								$script .= 'insert into '.$this->db->dbprefix.'field' .'(`'.implode('`,`', $f_columns).'`)';
								$values = array();
								foreach($f_columns as $column){
									if($column == 'fieldgroup_id'){
										$values[] = '@fg_id';
									}
									else if($column == 'module_id'){
										$values[] = '@module_id';
									}
									else{
										$values[] = '"'. $f[$column] .'"';
									}
								}
								$script .= ' values('. implode(',', $values) .');'."\r\n";
							}
						}
						$script .= "\r\n";
					}
					$script .= "\r\n";
			}
			
			//module listview
			$listview = $this->db->get_where('listview', array($this->key_field => $record_id))->result_array();
			if( sizeof( $listview ) > 0 ){
				foreach($listview as $index => $l){
						unset($l['listview_id']); //remove the listview id
						$l_columns = array_keys( $l );
						$script .= 'insert into '.$this->db->dbprefix.'listview' .'(`'.implode('`,`', $l_columns).'`)';
						$values = array();
						foreach($l_columns as $column){
							if($column == $this->key_field){
								$values[] = '@module_id';
							}
							else{
								$values[] = '"'. $l[$column] .'"';
							}
						}
						$script .= ' values('. implode(',', $values) .');'."\r\n";
				}
				$script .= "\r\n";
			}			
			
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', array('html' => $script));
			$mod_sqlscript = 'mod_'.str_replace(' ', '_', strtolower($module['short_name']) ).'.sql';
			header('Content-Disposition: attachment; filename="'.$mod_sqlscript.'"');			
		}			
		else{
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
	
	function _default_grid_actions( $module_link = "",  $container = "", $row = array() )
	{
	
		$record_id = $row['module_id'];
		
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";
		
		$actions = '<span class="icon-group">';
		
		$actions .= '<a tooltip="Toggle State" href="javascript:void(0)" class="icon-button '. ($row['inactive'] == 0 ? 'icon-16-active' : 'icon-16-xgreen-orb') .'" onclick="toggleModuleInactive($(this), \''.$record_id.'\')"></a>';
		
		$actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a><a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="'.$module_link.'" ></a><a class="icon-button icon-16-export" tooltip="Export" href="javascript:void(0)" module_link="'.$module_link.'" record_id="'.$record_id.'"></a><a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a></span>';
		return $actions;
	}

	/**
	 * Duplicate all the properties of a module, unlike export whick creates a sql script, this will save to database
	 * @return json
	 */
	function clone_module()
	{
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect( base_url().$this->module_link );
		}

		$response->msg = "";

		if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
		if( !$this->input->post( 'record_id' ) ){
			$response->msg = 'Insufficient data supplied!<br/>Please contact the System Administrator.';
			$response->msg_type = 'error';
		}

		if( $this->user_access[$this->module_id]['add'] == 1 ){
			$response->msg = 'You dont have sufficient privilege to execute the requested action! Please contact the System Administrator.';
			$response->msg_type = 'attention';
		}

		if( $response->msg === ""){
			$module_id = $this->input->post( 'record_id' );
			$module = $this->db->get_where('module', array('module_id' => $module_id));

			//delete setting files
			$this->_delete_nav_and_access();
		}

		$data['json'] = $response;
		$this->load->view( $this->userinfo['rtheme'].'/template/ajax', $data );
	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>