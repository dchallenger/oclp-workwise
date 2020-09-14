<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_position extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'User Settings';
		$this->listview_description = 'This module lists all defined user positions.';
		$this->jqgrid_title = "User Position List";
		$this->detailview_title = 'User Position Info';
		$this->detailview_description = 'This page shows detailed information about a particular user position';
		$this->editview_title = 'User Position Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about user position';
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
		if($this->user_access[$this->module_id]['edit'] == 1){
			parent::edit();		

			$this->db->where($this->key_field, $this->input->post('record_id'));
			$result = $this->db->get($this->module_table);

			if ($result->num_rows()) { 
				$this->load->vars(array('raw' => $result->row()));
			}

			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}	
			$data['content'] = 'editview';
			
			//other views to load
			$data['views'] = array('admin/user/position_portlet_config');
			$data['views_outside_record_form'] = array();
			
			// Get modules for notification config.
			$this->db->where('setup_notification', 1);
			$this->db->where('deleted', 0);

			$modules = $this->db->get('module');

			if ($modules->num_rows() > 0) {
				$data['modules'] = $modules->result();
			} else {
				$data['modules'] = false;
			}

			if ($data['modules']) {				
				foreach ($data['modules'] as $key => $module) {
					$this->db->select('user_position_approvers.*, user_position.position as approver_position');

					$this->db->where('user_position_approvers.position_id', $this->input->post('record_id'));
					$this->db->where('module_id', $module->module_id);
					
					$this->db->join('user_position', 'user_position.position_id = user_position_approvers.approver_position_id');
					$this->db->order_by('approver_no', 'desc');
					$result = $this->db->get('user_position_approvers');

					if ($result->num_rows() > 0) {
						$module->approvers = $result->result();
						$data['modules'][$key] = $module;
					}
				}
			}

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
		// START saving portlet config
		$visible = $this->input->post('visible');
		$access = $this->input->post('access');
		$this->db->order_by('portlet_name');
		$portlets = $this->db->get_where('portlet', array('deleted' => 0));
		if( $portlets->num_rows() > 0 ) {
			$portlet_config = array();
			foreach($portlets->result_array() as $index => $portlet){
				$portlet_config[$portlet['portlet_id']]['visible'] = $visible[$portlet['portlet_id']];
				$portlet_config[$portlet['portlet_id']]['access'] = $access[$portlet['portlet_id']];
			}
			$portlet_config = serialize($portlet_config);
			$this->db->where('position_id', $this->key_field_val);
			$this->db->update('user_position', array('portlet_config' => $portlet_config));
		}
		
		//clear affected user portlet state
		//get affected users
		$users = $this->db->get_where('user', array($this->key_field => $this->key_field_val));
		if( $users->num_rows() > 0 ){
			foreach($users->result() as $affected_user){
				$this->db->update('user_config', array('value' => ''), array('key' => 'portlet_state', 'user_id' => $affected_user->user_id));
			}
		}
		
		// END saving portlet config
		
		$notifications_post = $this->input->post('notifications');
		$condition = $this->input->post('condition');

		$this->db->where('position_id', $this->key_field_val);
		$this->db->delete('user_position_approvers');
		
		if (is_array($notifications_post)) {
			foreach ($notifications_post as $module_id => $positions) {
				foreach ($positions as $position_id => $types) {					
					$notifications[] = array(
						'module_id'   => $module_id, 
						'condition'   => $condition[$module_id], 
						'approver_position_id' => $position_id, 
						'email' 	  => ($types['email']) ? 1 : 0,
						'approver' 	  => ($types['approver']) ? 1 : 0,
						'approver_no' => $types['approver_no'],
						'position_id' => $this->key_field_val
						);					
				}
			}

			$this->db->insert_batch('user_position_approvers', $notifications);
		}
		
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}
	// END - default module functions
	
	// START custom module funtions
	
	function get_reporting_to()
	{
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url().$this->module_link);
		} else {
			$record_id  = $this->input->post('record_id');
			$company_id = $this->input->post('company_id');					

			$select = array('position', 'position_code', 'position_id');

			$this->db->select($select);

			//$this->db->where('company_id', $company_id);
			$this->db->where('deleted', 0);

			$this->db->order_by('position');

			$result = $this->db->get($this->module_table);

			$response['json']['positions'] = $result->result();

			$this->load->view('template/ajax', $response);
		}
	}

	function portlet_config($portlet_id)
	{

		if( $portlet_id > 0 ){

			$this->db->where('portlet_id',$portlet_id);
			$this->db->where('deleted',0);
			$portlet_result = $this->db->get('portlet');

			if( $portlet_result->num_rows() > 0 ){

				// START saving portlet config 
			
				$this->db->where('deleted', 0);
				// $this->db->where($this->key_field, 938);
				$positions = $this->db->get('user_position')->result();
				
				foreach ($positions as $key => $position) {
					$portlet_config = unserialize($position->portlet_config);
					$portlet_config[$portlet_id] = array('visible' => 1, 'access' => 'all');
					
					$portlet_config = serialize($portlet_config);
					$this->db->where('position_id',  $position->position_id);
					$this->db->update('user_position', array('portlet_config' => $portlet_config));

					//clear affected user portlet state
					// get affected users
				
					$users = $this->db->get_where('user', array($this->key_field => $position->position_id));
					if( $users->num_rows() > 0 ){
						foreach($users->result() as $affected_user){
							$this->db->update('user_config', array('value' => ''), array('key' => 'portlet_state', 'user_id' => $affected_user->user_id));
						}



					}
				}

				echo "Portlet successfully updated";

			}

		}		
		
		// END saving portlet config
	}

	/**
	 * Returns the positions in boxy listview format.
	 * Used in notifications UI.
	 * 
	 * @return json
	 */
	function positions_notification_boxy() {		
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url().$this->module_link);
		} 

		$data['container'] = $this->module.'-fmlink-container';
		$data['pager'] = $this->module.'-fmlink-pager';
		$data['fmlinkctr'] = $this->input->post('fmlinkctr');

		// Sets the flag for notification interface. Encoding so that the array can be passed to the jquery script.
		$data['other'] = base64_encode(serialize(array('module_id' => $this->input->post('module_id'))));

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_listview_in_boxy_grid_buttons();
		
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/listview_in_boxy.js"></script>';

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";

		$this->load->vars( $data );
		$boxy = $this->load->view($this->userinfo['rtheme']."/listview_in_boxy", "", true);

		$data['html'] = $boxy;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

	function _default_field_module_link_actions($keyfield_id = 0, $container = '', $fmlinkctr = 0, $row_data) {
		$module_id = unserialize(base64_decode($this->input->post('other')));

		if (is_array($module_id) && isset($module_id['module_id'])) {
			$module_id = $module_id['module_id'];

			$actions = '<span class="icon-group"><a class="icon-button icon-16-add" tooltip="Add" href="javascript:void(0)" onclick="add_approver(\'' . $keyfield_id . '\', \'' . $module_id . '\', \'' . $row_data['position'] . '\', \'' . $fmlinkctr . '\')"></a></span>';
			return $actions;		
		}		
		
		return parent::_default_field_module_link_actions($keyfield_id, $container, $fmlinkctr);
	}
	
	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
                
        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
        
				if ( $this->user_access[$this->module_id]['edit'] ) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
				
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_jd')) {
            $actions .= '<a class="icon-button icon-16-print print-jd" tooltip="Print JD" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        if ($this->user_access[$this->module_id]['delete']) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}	
	
	/**
	 * Returns the JD Form
	 * 
	 * @return json
	 */
	function get_jd_form(){
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url().$this->module_link);
		}
		
		$response->jd_form = $this->load->view( $this->userinfo['rtheme']. '/' . $this->module_link . '/edit-jd', array('record_id' => $this->input->post('record_id')), true );
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}	
	
	/**
	 * Returns the JD Items in tabular way
	 * 
	 * @return json
	 */
	function get_jd_items(){
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url().$this->module_link);
		}
		
		$position_id = $this->input->post('record_id');
		$response->jd_items = $this->_jd_items( $position_id );
		
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}
	
	function _jd_items( $position_id = 0, $show_action = true ){
		$jd_items = "";
		//get the jd group
		$jdgroups = $this->db->get_where('user_position_jdgroup', array('deleted' => 0));
		if( $jdgroups->num_rows() > 0 ){
			$jdgroups = $jdgroups->result_array();
			foreach( $jdgroups as $jdg_index => $jdgroup ){
				$jdgroups[$jdg_index]['has_item'] = false;
				//get subgroup/key competencies
				$jdsubgroups = $this->db->get_where('user_position_jdsubgroup', array('deleted' => 0, 'jdgroup_id' => $jdgroup['jdgroup_id']));
				if( $jdsubgroups->num_rows() > 0 ){
					$jdsubgroups = $jdsubgroups->result_array();
					foreach( $jdsubgroups as $jdsg_index => $jdsubgroup ){
						$jdsubgroups[$jdsg_index]['has_item'] = false;
						//get items for subgroup
						$jditems = $this->db->get_where('user_position_jditem', array('deleted' => 0, 'jdsubgroup_id' => $jdsubgroup['jdsubgroup_id'], 'position_id' => $position_id ));
						if( $jditems->num_rows() > 0 ){
							$jditems = $jditems->result_array();
							$jdsubgroups[$jdsg_index]['has_item'] = true;
							$jdgroups[$jdg_index]['has_item'] = true;
							$jdsubgroups[$jdsg_index]['jditems'] = $jditems;
							$jdgroups[$jdg_index]['jdsubgroups'][$jdsg_index] = $jdsubgroups[$jdsg_index];
						}
					}
				}
			}
			
			foreach( $jdgroups as $jdg_index => $jdgroup ){
				if($jdgroup['has_item']){
					if( $show_action ){
						$w1 = 'width="5%"';
						$w2 = 'width="70%"';
						$w3 = 'width="10%"';
						$w12 = 'width="75%"';
					}
					else{
						$w1 = 'width="5%"';
						$w2 = 'width="80%"';
						$w3 = 'width="15%"';
						$w12 = 'width="85%"';
					}
					
					$jd_items .= '<tr style="background-color: #333333; color: white"><td colspan="2" '.$w12.'><strong>'.$jdgroup['jdgroup'].'</strong></td><td align="center" '.$w3.'>WEIGHTS</td>';
					if( $show_action ) $jd_items .= '<td width="15%">&nbsp;<td/>';
					$jd_items .= '</tr>';
					
					foreach( $jdgroup['jdsubgroups'] as $jdsg_index => $jdsubgroup ){
						if( $show_action )
							$jd_items .= '<tr style="background: #c0c0c0"><td colspan="4"><em>'. $jdsubgroup['jdsubgroup'] .'</em></td></tr>';
						else
							$jd_items .= '<tr style="background: #c0c0c0"><td colspan="3"><em>'. $jdsubgroup['jdsubgroup'] .'</em></td></tr>';
						foreach( $jdsubgroup['jditems'] as $item_index => $jditem ){
							$jd_items .= '<tr>';
							$jd_items .= '<td align="center" '.$w1.'>'. ($item_index + 1) .'</td>';
							$jd_items .= '<td align="left" '.$w2.'>'. $jditem['jditem'] .'</td>';
							$jd_items .= '<td align="center" '.$w3.'>'. ( $jditem['weight'] > 0 ? $jditem['weight'] .'%' : "" ) .'</td>';
							if( $show_action ) $jd_items .= '<td align="center" width="15%"><a onclick="edit_jditem_detail( \'-1\', '. $jditem['jditem_id'] .' )" href="javascript:void(0);" class="icon-button icon-16-add">Add Item Detail</a><a onclick="edit_jd_item( '. $jditem['jditem_id'] .', '. $position_id .' )" href="javascript:void(0);" class="icon-button icon-16-edit">Edit Item</a><a onclick="delete_jditem( '. $jditem['jditem_id'] .' )" href="javascript:void(0);" class="icon-button icon-16-delete">Delete Item</a></td>';
							$jd_items .= '</tr>';
							//check for item details
							$jditem_details = $this->db->get_where('user_position_jditem_detail', array('deleted' => 0, 'jditem_id' => $jditem['jditem_id']));
							if( $jditem_details->num_rows() > 0 ){
								foreach( $jditem_details->result_array() as $index => $detail ){
									$jd_items .= '<tr>';
									$jd_items .= '<td align="right"  '.$w1.'>'. ($item_index + 1) . (chr( $index + 65)) .'.&nbsp;</td>';
									$jd_items .= '<td align="left"  '.$w2.'>'. $detail['jditem_detail'] .'</td>';
									$jd_items .= '<td align="center"  '.$w3.'></td>';
									if( $show_action ) $jd_items .= '<td align="center" width="15"><a onclick="edit_jditem_detail( '. $detail['jditem_detail_id'] .', '. $jditem['jditem_id'] .')" href="javascript:void(0);" class="icon-button icon-16-edit">Edit Item Detail</a><a onclick="delete_jditem_detail( '. $detail['jditem_detail_id'] .' )" href="javascript:void(0);" class="icon-button icon-16-delete">Delete Item Detail</a></td>';
									$jd_items .= '</tr>';
								}
							}
						}
					}
				}
			}
		}
		
		if( !empty( $jd_items ) ){
			$jd_items = '<table cellpadding="jd-table" width="100%;" border="1" cellpadding="10" cellspacing="0">'. $jd_items .'</table>';
		}
		
		return $jd_items;
	}
	
	
	/**
	 * Creates a PDF File of Positions JD
	 * For Appraisal Purposes
	 *  
	 * @return void
	 */
	function print_jd( $record_id = 0  ){
		if(!$this->user_access[$this->module_id]['print'] == 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);			
		}

		if ( $record_id == 0 ) $record_id = $this->input->post('record_id');
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template($this->module_id, 'JD_APPRAISAL');
		
		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);
		if ($check_record->exist) {
			$position = $this->db->get_where('user_position', array( $this->key_field =>  $record_id))->row();
			$html = $this->_jd_html( $record_id );
			
			// Prepare and output the PDF.
			$this->pdf->SetFontSize( 8 );
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date($this->config->item('display_datetime_format_compact')).'-'. $position->position_code .'-JD.pdf', 'D');
		}
		else {
			$this->session->set_flashdata('flashdata', 'The Data you are trying to access does not exist.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}
	
	function get_jd_html() {
		$record_id = $this->input->post('record_id');

		$data['json']['jd_items'] = $this->_jd_html($record_id);

		$this->load->view('template/ajax', $data);
	}

	/**
	 * Employee JD
	 * For Viewing Purposes
	 *  
	 * @return void
	 */
	function _jd_html( $record_id ){
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template($this->module_id, 'JD_APPRAISAL');
		
		$vars = get_record_detail_array( $record_id );
		$position = $this->db->get_where('user_position', array( $this->key_field =>  $record_id))->row();
		$vars['next_level_superior'] = "";
		$next_level_superiors = $this->db->get_where('user_position', array( $this->key_field =>  $position->reporting_to));
		$next_level_superiors = $next_level_superiors->row();
		//$this->db->where('position_id',$next_level_superiors->reporting_to );
		$next_level_superior = $this->db->get_where('user_position', array( $this->key_field =>  $next_level_superiors->reporting_to ));
		if( $next_level_superior->num_rows() == 1 ){
			$next_level_superior = $next_level_superior->row();
			$vars['next_level_superior'] = $next_level_superior->position;
		}
		
		$company = $this->db->get_where('user_company', array( 'company_id' =>  $position->company_id))->row();
		$vars['company'] = $company->company;
		
		//supervises
		$vars['ps1'] = "None";
		$vars['ps2'] = "";
		$vars['ps3'] = "";
		$vars['ps4'] = "";
		$vars['ps5'] = "";
		$vars['supervises'] = "";
		if( !empty( $position->supervises ) ){
			$supervises_id = explode( ',', $position->supervises );
			foreach( $supervises_id as $index => $position_id ){
				$supervise = $this->db->get_where('user_position', array( $this->key_field =>  $position_id))->row();
				$vars['ps'.($index+1)] = $supervise->position;
				$vars['supervises'] .= '<tr>
					<td width="7%" align="right">'. (chr( $index + 65)) .'.</td>
					<td width="3%">&nbsp;</td>
					<td width="90%">'. $supervise->position .'</td>
				</tr>';
			}
		}
		$vars['pc1'] = "None";
		$vars['pc2'] = "";
		$vars['pc3'] = "";
		$vars['pc4'] = "";
		$vars['pc5'] = "";
		$vars['coordinates_with'] = "";
		if( !empty( $position->coordinates_with ) ){
			$coordinates_with_id = explode( ',', $position->coordinates_with );
			foreach( $coordinates_with_id as $index => $position_id ){
				$coordinates_with = $this->db->get_where('user_position', array( $this->key_field =>  $position_id))->row();
				$vars['pc'.($index+1)] = $coordinates_with->position;
				$vars['coordinates_with'] .= '<tr>
					<td width="7%" align="right">'. (chr( $index + 65)) .'.</td>
					<td width="3%">&nbsp;</td>
					<td width="90%">'. $coordinates_with->position .'</td>
				</tr>';
			}
		}
		
		$vars['jd_details'] = $this->_jd_items( $record_id, false );
		//dbug($vars);
		$html = $this->template->prep_message($template['body'], $vars, false, true);
		return $html;
	}
	
	/**
	 * Employee JD
	 * For Viewing Purposes
	 *  
	 * @return void
	 */ 
	function my_jd(){
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template($this->module_id, 'JD_APPRAISAL');
		
		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist( $this->userinfo['position_id'] );
		if ($check_record->exist) {
			$data['buttons'] = $this->module_link . '/print';
			$data['jd_html'] = $this->_jd_html( $this->userinfo['position_id'] );
			$data['content'] = $this->module_link . '/my_jd';
			//dbug($this->_jd_html( $this->userinfo['position_id'] ));

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
		else {
			$this->session->set_flashdata('flashdata', 'The Data you are trying to access does not exist.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}
	 
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>
