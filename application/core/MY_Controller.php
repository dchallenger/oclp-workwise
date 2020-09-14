<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
	var $user;

	var $module;
	var $module_link;
	var $user_access;

	var $module_layout;
	var $show_action_column;
	var $action_column_width;
	var $action_column_align;
	var $show_multiselect_column;
	var $module_table;
	var $key_field;
	var $key_field_val;
	var $default_sort_col;
	var $related_table;

	var $listview_title;
	var $listview_description;
	var $jqgrid_title;
	var $grid_grouping;
	var $detailview_title;
	var $row_num = 25;	
	var $detailview_description;
	var $editview_title;
	var $editview_description;

	var $listview_qry;
	var $listview_column_names;
	var $listview_columns;
	var $listview_fields;
	
	var $filter;

	public $is_admin, $is_superadmin;

	private $_msg, $_msg_type;

	public function __construct()
	{
		parent::__construct();		

		$this->user = $this->session->userdata('user');
		
		if( !$this->user || empty( $this->user->user_id ) ){
			if(IS_AJAX){
				$response->msg_type = 'error';
				$response->msg = 'Session expired, please refresh your browser.';	
				$this->load->view('template/ajax', array('json' => $response));
			}
			else{
				$this->session->set_userdata('uri_request', $this->uri->uri_string());
				redirect('login');
			}
		}		

		// Replace the controller name if ever it has been overridden.
		if (CLIENT_DIR != '') {
			$this->extended = true;
			$this->module = $controller = str_replace(CLIENT_DIR . '_', '', $this->router->fetch_class());
		} else {
			$this->module = $controller = $this->router->fetch_class();
			$this->extended = false;
		}

		if( $this->session->userdata('uri_request') && $controller != 'login' ){
			$redirect = $this->session->userdata('uri_request');
			$this->session->unset_userdata( 'uri_request' );
			redirect($redirect);
		}

		$this->load->helper('export');
		//user authenticated
		$this->method = $data['method']    = $this->router->fetch_method();
		$this->record_id = $data['record_id'] = $this->uri->rsegment(3);

		//get the modules url
		$uri_segments = $this->uri->segment_array();
		if( $data['method'] === 'index' && $this->module != 'dashboard' ) array_push( $uri_segments, 'index' );
		foreach($uri_segments as $index => $segment){
			if( $segment === $data['method'] && $controller == $uri_segments[ $index - 1]  ){
				$module_link_segments = array();
				for( $i = 1; $i < $index; $i++){
					$module_link_segments[] = $uri_segments[$i];
				}
				$this->module_link = implode( '/', $module_link_segments );
				array_pop( $module_link_segments );
				$this->parent_path = implode( '/', $module_link_segments );
				break;
			}
		}

		if( $this->module_link == "" ){
			$this->module_link = $this->module;
			$this->parent_path = "";
		}

		//route route to profile
		if( $this->uri->segment(1) == "profile" ){
			$this->parent_path = "admin";
			$this->module_link = "admin/user";
		}
		
		//set module name and id
		$this->_set_module_detail( $this->module_link );
		if( !IS_AJAX ){
			$this->hdicore->_set_breadcrumbs();
		}
				
		$data['user'] = $this->user;

    	$this->load->library('encrypt');
		$this->load->helper('file');
		$app_directories =  $this->hdicore->_get_config('app_directories');
		
		$user_settings = $app_directories['user_settings_dir'] . $this->user->user_id . '.php';
		if( !file_exists( $user_settings ) ){
			//create user nav and access file on the fly

			//set navigation and access
			$navs = array();
			$this->user_access = array();
			$user = $this->hdicore->_get_userinfo( $this->user->user_id );
			$this->user_access = $this->hdicore->_create_user_access_file( $user );
			$navs = $this->hdicore->_create_navigation( 0, $this->user_access );
			$userinfo = (array) $this->hdicore->_get_userinfo( $this->user->user_id );

			//write data to file
            $to_write = '<?php $config_hash = "' . $this->encrypt->encode('$navs = ' . var_export($navs, true) . ";\r\n". '$this->user_access = '. var_export($this->user_access, true) . ";\r\n". '$this->userinfo = $userinfo = ' . var_export($userinfo, true) . ";") . '";';

			write_file($user_settings, $to_write);
		}	
		
		require_once( $user_settings );                        
      	eval($this->encrypt->decode($config_hash));	
      	
      	if((!empty($this->userinfo['photo']) && !file_exists($this->userinfo['photo']) ) || empty($this->userinfo['photo'])) $this->userinfo['photo'] = $this->userinfo['theme'].'/images/no-photo.jpg';

		if( !method_exists($this, '_visibility_check') ){
			
			$this->hdicore->_visibility_check();
		}
		else{
			$this->_visibility_check();	
		}

		if( $this->sensitivity_filter ){
			$role_settings = $app_directories['role_settings_dir'] . $this->userinfo['role_id'] . '.php';
			if( !file_exists( $role_settings ) ){
				$settings = $this->hdicore->_get_role_settings( $this->userinfo['role_id'] );
				$to_write = '<?php $config_hash = "' . $this->encrypt->encode('$this->sensitivity = ' . var_export($settings['sensitivity'], true) . ";\r\n" . ";") . '";';
				write_file($role_settings, $to_write);
			}

			require_once( $role_settings );                        
      		eval($this->encrypt->decode($config_hash));		
		}

		if ($this->_check_if_guest()) {			
			if ($this->router->fetch_class() != 'appform' && $this->router->fetch_method() != 'edit') {
				redirect('recruitment/appform/edit');			
			}
		}

		//check if user is admin
		$admin_check = $this->_admin_check();	
		$data['is_superadmin'] = $this->is_superadmin = $admin_check[0];
		$data['is_admin'] = $this->is_admin = $admin_check[1];
		$data['is_recruitment'] = $this->is_recruitment = $admin_check[2];

		$data['header_nav'] = $navs;
		
		//meta variables
		if( !IS_AJAX ) {
			$data['meta'] = $this->hdicore->_get_meta();
			$this->config->set_item('meta', $data['meta']);
		}

		// quick_links
		$quicklink_file = $app_directories['system_settings_dir'].'quick_links.php';
		if(file_exists($quicklink_file)){
			require_once($quicklink_file);
		}else{
			$quick_links = array();
			
			//get quicklink groups
			$quicklink_groups = $this->db->get_where('quicklink_group', array('deleted' => 0));
			foreach($quicklink_groups->result() as $ql_group):
				//get links for group
				$this->db->order_by('sequence');
				$result = $this->db->get_where('quicklink', array('deleted' => 0, 'visible' => 1, 'quicklink_group_id' => $ql_group->quicklink_group_id));
				if($result->num_rows() > 0) :
					$quick_links[$ql_group->quicklink_group_id]['quicklink_group'] = $ql_group->quicklink_group;
					foreach($result->result() as $row):
						$quick_links[$ql_group->quicklink_group_id]['links'][$row->quicklink_id] = array(
							'quicklink_name' => $row->quicklink_name,
							'quicklink_link' => $row->quicklink_link,
							'quicklink_icon' => $row->quicklink_icon,
						);
					endforeach;
				endif;	
			endforeach;
			
			//write data to file
			$to_write = '<?php $quick_links = ' . var_export($quick_links, true) . ';?>';
			write_file($quicklink_file, $to_write);
		}
		$data['quick_link'] = $quick_links;
		$data['footer_widget_state'] = $this->hdicore->_get_user_config('footer_widget_state', $this->user->user_id);
		
		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}
		
		$this->load->vars($data);

		$this->load->helper('audit_log_trail');

		$no_segment = $this->uri->total_segments();
		$mode = $this->uri->segment($no_segment);

		if ($this->input->post('record_id') == -1){
			$mode = "add";
		}

		if($this->module != "chat" && in_array( $mode, $this->config->item('for_auditing') )){
			// access log

			$postvar = isset($_POST) ? $_POST : '';
			$accessfile =  'logs/accesslog/'.date('Y-m-d').'.txt';

			$after_edit  = flatten($postvar);

			$after_edit = unset_unnecessary_field($after_edit);

			$after_edit = reassign($after_edit);

			$before_edit_sess = $this->session->userdata('before_edit');
			$before_edit = array();
			$arr_label = array();	
			$before_edit_val_comma = '';
			$after_edit_val_comma = '';

			switch (strtolower($mode)) {
				case 'save':
				case 'edit':
					if ($before_edit_sess != ''){
						$arr = re_arrange_array($before_edit_sess);
						$before_edit = $arr['ar_name'];
						$arr_label = $arr['ar_label'];
						$before_edit = reassign($before_edit);					
					}

					$before_edit_val_comma = '';
					if (sizeof($before_edit) > 0){
						$before_edit_diff = array_intersect_key($before_edit, array_diff($after_edit, $before_edit));
						$before_value = array_values($before_edit_diff);				
						if (sizeof($before_value) > 0){
							$before_edit_val_comma =  implode('|', $before_value);
						}

						if (!preg_match('/[a-zA-Z0-9]/', $before_edit_val_comma)){
							$before_edit_val_comma = '';
						}				
					}

					$after_edit_val_comma = '';
					if (sizeof($after_edit) > 0){
						$after_edit_diff = array_diff($after_edit, $before_edit);
						$after_value = array_values($after_edit_diff);
						if (sizeof($after_value) > 0){
							$after_edit_val_comma =  implode('|', $after_value);
						}				
					}

					$before_label_used = array_intersect_key($arr_label,array_diff($before_edit, $after_edit));
					$after_label_used = array_intersect_key($arr_label,array_diff($after_edit, $before_edit));

					$combined_label_used = array_unique(array_merge($before_label_used,$after_label_used));

					$combined_label_used_comma = '';
					if (sizeof($combined_label_used) > 0){
						$combined_label_used_comma =  implode('|', $combined_label_used);
					}

					$after_label_used_comma = '';
					if (sizeof($after_label_used) > 0){
						$after_label_used_comma = implode('|', $after_label_used);
					}
					break;
				case 'ajax_save':					
				case 'add':
						$after_label_used_comma = '';
						$after_edit_val_comma = filter($after_edit);						
					break;					
				case 'delete':
						$after_edit_val_comma = $this->input->post('record_id');
					break;
				case 'ajax_save':
						$mode = 'save';
					break;	
				case 'change_status':
						$after_label_used_comma = '';
						$after_edit_val_comma = filter($after_edit);
						if ($after_edit_val_comma == '' && $this->input->post('status') != ''){
							$after_edit_val_comma = $this->input->post('status');
						}						
						$mode = 'change_status';
					break;		
				case 'approve_request':
						$after_label_used_comma = '';
						$after_edit_val_comma = 'Approved';						
						$mode = 'approve_request';
					break;
				case 'decline_request':
						$after_label_used_comma = '';
						$after_edit_val_comma = 'Decline';						
						$mode = 'decline_request';
					break;			
			}

			if ($after_edit_val_comma == '' && ($mode == 'ajax_save' || $mode == 'add')){
				if ($before_edit_sess != ''){
					$arr = re_arrange_array($before_edit_sess);
					$before_edit = $arr['ar_name'];
					$arr_label = $arr['ar_label'];
					$before_edit = reassign($before_edit);					
				}

				$before_edit_val_comma = '';
				if (sizeof($before_edit) > 0){
					$before_edit_diff = array_intersect_key($before_edit, array_diff($after_edit, $before_edit));
					$before_value = array_values($before_edit_diff);				
					if (sizeof($before_value) > 0){
						$before_edit_val_comma =  implode('|', $before_value);
					}

					if (!preg_match('/[a-zA-Z0-9]/', $before_edit_val_comma)){
						$before_edit_val_comma = '';
					}				
				}

				$after_edit_val_comma = '';
				if (sizeof($after_edit) > 0){
					$after_edit_diff = array_diff($after_edit, $before_edit);
					$after_value = array_values($after_edit_diff);
					if (sizeof($after_value) > 0){
						$after_edit_val_comma =  implode('|', $after_value);
					}				
				}

				$before_label_used = array_intersect_key($arr_label,array_diff($before_edit, $after_edit));
				$after_label_used = array_intersect_key($arr_label,array_diff($after_edit, $before_edit));

				$combined_label_used = array_unique(array_merge($before_label_used,$after_label_used));

				$combined_label_used_comma = '';
				if (sizeof($combined_label_used) > 0){
					$combined_label_used_comma =  implode('|', $combined_label_used);
				}

				$after_label_used_comma = '';
				if (sizeof($after_label_used) > 0){
					$after_label_used_comma = implode('|', $after_label_used);
				}
			}

			if ( in_array( $mode, $this->config->item('for_auditing') ) ){
				$after_edit_val_comma = preg_replace(array('/all|/','/personal|/'), '', $after_edit_val_comma);
				$after_edit_val_comma = preg_replace('/^\|/', '', $after_edit_val_comma);

				$module = $this->db->get_where('module', array('module_id' => $this->module_id, 'deleted' => 0))->row();

				if ($mode == 'change_status' && ($after_edit_val_comma == '' || is_numeric($after_edit_val_comma))){
					$form_status = '';

					if ($this->module == 'employee_program' || $this->module == 'post_graduate'){
						$result = $this->db->get_where('training_application_status',array('training_application_status_id' => $this->input->post('status')));
						if ($result && $result->num_rows() > 0){
							$form_status = $result->row()->training_application_status;
						}
					}
					else{
						$result = $this->db->get_where('form_status',array('form_status_id' => $this->input->post('form_status_id')));
						if ($result && $result->num_rows() > 0){
							$form_status = $result->row()->form_status;
						}						
					}

					$after_edit_val_comma = $form_status;
				}

				$info_array = array('DATE(date_time)' => date('Y-m-d'),
									'modules' => $module->short_name, 
									'field_name' => $after_label_used_comma,
									'original_value' => $before_edit_val_comma,
									'to_what_value' => $after_edit_val_comma,
									'mode' => ucfirst($mode),
									'user' => $this->userinfo['firstname'].",".$this->userinfo['lastname'],
									'ip_address' => $this->input->ip_address(),
									'computer_name' => gethostname()
								);

				$this->db->where($info_array);
				$this->db->delete('audit_log_trail');

				unset($info_array['DATE(date_time)']);
				$info_array['date_time'] = date('Y-m-d G:i:s');				

				if (isset($this->userinfo['user_id'])){
					$info_array['user_id'] = $this->userinfo['user_id'];
				}

				if ($mode == 'edit'){
					if ($after_label_used_comma != '' || $before_edit_val_comma != ''){
						$this->db->insert('audit_log_trail',$info_array);
					}
				}
				else{					
					if ($after_edit_val_comma != ''){
						$this->db->insert('audit_log_trail',$info_array);
					}						
				}
			}
		}

		$this->_load_module_packages();	
		
		// Reload the client.php config, this time the client packages have been added to
		// the config file paths, if any, this will override the default config/client.php
		$this->load->config('client');
	}

   // --------------------------------------------------------------------    
    
    /**
     * Have to remap the call to the controller in case there are some uri segments issues.
     */    
    function _remap($method, $params = array())
    {
        if (method_exists($this, $method))
        {
            if (isset($params[0]) && $method == $params[0])
            {
                unset($params[0]);
            }
            
            return call_user_func_array(array($this, $method), $params);
        }
    }

    // --------------------------------------------------------------------    
    
    /**
     * Loads the dir of all extensions that we have.
     */      
    private function _load_module_packages()
    {
		$this->load->add_package_path(MODPATH . CLIENT_DIR);
    }

	private function _check_if_guest() {
		$this->db->where('profile_id', $this->config->item('guest_profile_id'));
		$this->db->where('role_id', $this->userinfo['role_id']);

		$result = $this->db->get('role_profile');


		return $result->num_rows() > 0;	
	}

	function _set_module_detail( $class_path = '' )
	{
		$module = $this->db->get_where('module', array('class_path' => $class_path, 'deleted' => 0))->row();
		
		$this->module_id = $module->module_id;
		$this->module_name = $module->short_name;
		$this->module_table = $module->table;
		$this->module_wizard_form = $module->wizard_form;
		$this->key_field = $module->key_field;
		$this->show_action_column = $module->show_action_column == 1 ? true : false;
		$this->action_column_width = $module->action_column_width;
		$this->action_column_align = $module->action_column_align;
		$this->show_multiselect_column = $module->show_multiselect_column;
		$this->module_icon = $module->sm_icon;
		$this->sensitivity_filter = $module->sensitivity_filter;	
	}

	function previous_page()
	{
		if(IS_AJAX){
			$breadcrumbs = $this->session->userdata('breadcrumbs');

			if( !$breadcrumbs ){
				$this->session->set_userdata('breadcrumbs', array());
				$breadcrumbs = array();
			}

			$response->previous_page = sizeof($breadcrumbs) > 1 ? $breadcrumbs[sizeof($breadcrumbs) - 2] : "false";

			//pop the current page
			if(sizeof($breadcrumbs) > 0) $pop = array_pop($breadcrumbs);
			$this->session->set_userdata('breadcrumbs', $breadcrumbs);

			$data['json'] = $response;
			$this->load->view( $this->userinfo['rtheme'].'/template/ajax', $data );
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
	
	function back_to_list()
	{
		if(IS_AJAX){
			$breadcrumbs = $this->session->userdata('breadcrumbs');
			if( !$breadcrumbs ){
				$this->session->set_userdata('breadcrumbs', array());
				$breadcrumbs = array();
			}

			$response->root_page = sizeof($breadcrumbs) > 0 ? $breadcrumbs[0] : "false";

			if($response->root_page != "false"){
				//reset breadcrumbs
				$breadcrumbs = array($response->root_page);
				$pop = array_pop($breadcrumbs);
				$this->session->set_userdata('breadcrumbs', $breadcrumbs );
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
	
	function _get_messages( $user_id )
	{
		$qry = "SELECT a.*, b.message_type
		FROM {$this->db->dbprefix}message a
		LEFT JOIN {$this->db->dbprefix}message_type b ON b.message_type_id = a.message_type_id
		WHERE a.user_id={$user_id}
		ORDER BY a.created_date ASC";
		$query = $this->db->query($qry);
		return $query->result();
	}
	
	private function _admin_check($module_id = null)
	{
		if (is_null($module_id)) {
			$module_id = $this->module_id;
		}

		$response->is_superadmin = $response->is_admin = FALSE;
		$response->is_recruitment = FALSE;
		
		//get profiles associated with tole
		$profiles = $this->hdicore->get_role_profile( $this->userinfo['role_id'] );
		if( in_array( 1, $profiles) && file_exists('settings/system/access.txt') ) {	
			$hash = file_get_contents('settings/system/access.txt');
			if ($hash == base64_encode(
				md5(
					$this->userinfo['login'] . 
					$this->userinfo['password'] . 
					$this->userinfo['email'] . 
					$this->config->item('encryption_key')
				)
				)
			){				
				$response->is_superadmin = TRUE;
			}
		}
		
		//if ( in_array( 2, $profiles) ) $response->is_admin = TRUE;
		$response->is_recruitment = $this->user_access[$module_id]['post'];
		
		if (IS_AJAX && $this->uri->segment(2) == 'admin_check'){					
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);				
		}
		else{
			return array($response->is_superadmin, $response->is_admin, $response->is_recruitment);
		}

	}

	function is_recruitment($module_id = null)
	{
		if (is_null($module_id)) {
			$module_id = $this->module_id;
		}

		return ($this->user_access[$module_id]['post'] == 1);
	}

	/* START List View Functions */
	/**
	 * Available methods to override listview.
	 * 
	 * A. _append_to_select() - Append fields to the SELECT statement via $this->listview_qry
	 * B. _set_filter()       - Add aditional WHERE clauses	 
	 * C. _custom_join
	 * 
	 * @return json
	 */
	function listview()
	{
		$response->msg = "";

		$page = $this->input->post('page');
		$limit = $this->input->post('rows'); // get how many rows we want to have into the grid
		$sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
		$sord = $this->input->post('sord'); // get the direction
		$related_module = ( $this->input->post('related_module') ? true : false );

		$view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true ;

		//set columnlist and select qry
		$this->_set_listview_query( '', $view_actions );

		//set Search Qry string
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;

		if( $this->module == "user" && (!$this->is_admin && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';


		if (method_exists($this, '_append_to_select')) {
			// Append fields to the SELECT statement via $this->listview_qry
			$this->_append_to_select();
		}

		if (method_exists($this, '_custom_join')) {
			$this->_custom_join();
		}

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		if(!empty( $this->filter ) ) $this->db->where( $this->filter );
		if( $this->sensitivity_filter ){
			$fields = $this->db->list_fields($this->module_table);
			if(in_array('sensitivity', $fields) && isset($this->sensitivity[$this->module_id])){
				$this->db->where($this->module_table.'.sensitivity IN ('.implode(',', $this->sensitivity[$this->module_id]).')');
			}
			else{
				$this->db->where($this->module_table.'.sensitivity IN (0)');	
			}	
		}

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

		//get list
		$total_records =  $this->db->count_all_results();
		//$response->last_query = $this->db->last_query();
		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{
			$total_pages = $total_records > 0 ? ceil($total_records/$limit) : 0;
			$response->page = $page > $total_pages ? $total_pages : $page;
			$response->total = $total_pages;
			$response->records = $total_records;

			/* record query */
			//build query
			$this->_set_left_join();
			$this->db->select($this->listview_qry, false);
			$this->db->from($this->module_table);

			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
			if( $this->sensitivity_filter ){
				if(in_array('sensitivity', $fields) && isset($this->sensitivity[$this->module_id])){
					$this->db->where($this->module_table.'.sensitivity IN ('.implode(',', $this->sensitivity[$this->module_id]).')');
				}	
				else{
					$this->db->where($this->module_table.'.sensitivity IN (0)');	
				}	
			}

			if (method_exists($this, '_set_filter')) {
				$this->_set_filter();
			}

			if (method_exists($this, '_custom_join')) {
				// Append fields to the SELECT statement via $this->listview_qry
				$this->_custom_join();
			}
			
			if($sidx != ""){
				$this->db->order_by($sidx, $sord);
			}
			else{
				if( is_array($this->default_sort_col) ){
					$sort = implode(', ', $this->default_sort_col);
					$this->db->order_by($sort);
				}
			}
			$start = $limit * $page - $limit;
			$this->db->limit($limit, $start);
			
			$result = $this->db->get();
			
			//$response->last_query = $this->db->last_query();

			//check what column to add if this is a related module
			if($related_module){
				foreach($this->listview_columns as $column){                                    
					if($column['name'] != "action"){
						$temp = explode('.', $column['name']);
						if(strpos($this->input->post('column'), ',')){
							$column_lists = explode( ',', $this->input->post('column'));
							if( sizeof($temp) > 1 && in_array($temp[1], $column_lists ) ) $column_to_add[] = $column['name'];
						}
						else{
							if( sizeof($temp) > 1  && $temp[1] == $this->input->post('column')) $this->related_module_add_column = $column['name'];
						}
					}
				}
				//in case specified related column not in listview columns, default to 1st column
				if( !isset($this->related_module_add_column) ){
					if(sizeof($column_to_add) > 0)
						$this->related_module_add_column = implode('~', $column_to_add );
					else
						$this->related_module_add_column = $this->listview_columns[0]['name'];
				}
			}

			if( $this->db->_error_message() != "" ){
				$response->msg = $this->db->_error_message();
				$response->msg_type = "error";
			}
			else{
				$response->rows = array();
				if($result->num_rows() > 0){
					$this->load->model('uitype_listview');
					$ctr = 0;

					foreach ($result->result_array() as $row){
						$cell = array();
						$cell_ctr = 0;
						foreach($this->listview_columns as $column => $detail){
							if( preg_match('/\./', $detail['name'] ) ) {
								$temp = explode('.', $detail['name']);
								$detail['name'] = $temp[1];
							}
							
							if(sizeof(explode(' AS ', $detail['name'])) > 1 ){
								$as_part = explode(' AS ', $detail['name']);
								$detail['name'] = strtolower( trim( $as_part[1] ) );
							}
							else if(sizeof(explode(' as ', $detail['name'])) > 1 ){
								$as_part = explode(' as ', $detail['name']);
								$detail['name'] = strtolower( trim( $as_part[1] ) );
							}
							
							if( $detail['name'] == 'action'  ){
								if( $view_actions ){
									$cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions( $row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr'), $row ) : $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row ) );
									$cell_ctr++;
								}
							}else{
								if( $this->listview_fields[$cell_ctr]['encrypt'] ){
									$row[$detail['name']] = $this->encrypt->decode( $row[$detail['name']] );
								}

								if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33, 35, 36, 37, 40) ) ){
									$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
									$cell[$cell_ctr] = $this->uitype_listview->fieldValue( $this->listview_fields[$cell_ctr] );
								}
								else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 3 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] ) && $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] == 'Query' ) ){
									$cell[$cell_ctr] = "";
									foreach($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val)
									{
										if($row[$detail['name']] == $picklist_val['id']) $cell[$cell_ctr] = $picklist_val['value'];
									}
								}
								else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 39 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['type'] ) && $this->listview_fields[$cell_ctr]['other_info']['type'] == 'Query' ) ){
									$cell[$cell_ctr] = "";
									foreach($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val)
									{
										if($row[$detail['name']] == $picklist_val['id']) $cell[$cell_ctr] = $picklist_val['value'];
									}
								}
								else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 39 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['type'] ) && $this->listview_fields[$cell_ctr]['other_info']['type'] != 'Query' ) ){
									$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
									$cell[$cell_ctr] = $this->uitype_listview->fieldValue( $this->listview_fields[$cell_ctr] );
								}
								else{
									$cell[$cell_ctr] = in_array('I', $this->listview_fields[$cell_ctr]['datatype']) || in_array('F', $this->listview_fields[$cell_ctr]['datatype']) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
								}
								$cell_ctr++;
							}
						}
						$response->rows[$ctr]['id'] = $row[$this->key_field];
						$response->rows[$ctr]['cell'] = $cell;
						$ctr++;
					}
				}
			}
		}
		
		$data['json'] = $response;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

	function _get_fieldgroup_fields($fieldgroup_id = 0, $quick_edit_flag = false)
	{
		$this->db->order_by('sequence');
		//$where = array("fieldgroup_id" => $fieldgroup_id, "visible" => 1, 'deleted' => 0);
		$where = array("fieldgroup_id" => $fieldgroup_id, 'deleted' => 0);
		if( $quick_edit_flag ) $where['quick_edit'] = 1;
		return $this->db->get_where('field', $where);
	}

	function _set_left_join()
	{
		//build left join for related tables
		foreach($this->related_table as $table => $column){
			if( strpos($table, " ") ){
				$letter = explode(" ", $table);
				if($this->module == "module" && $column == "module_id" && $this->module == "module")
					$this->db->join($table, $letter[1] .'.module_id='. $this->module_table .'.parent_id', 'left');
				else
					$this->db->join($table, $letter[1] .'.'. $column[0] .'='. $this->module_table .'.'. $column[1], 'left');
			}
			else{
				$this->db->join($table, $table .'.'. $column[0] .'='. $this->module_table .'.'. $column[1], 'left');
			}
		}
	}

	function _set_listview_query( $listview_id = '', $view_actions = true )
	{
		$record_detail = $this->_record_detail( -1 );

		//get the listview
		if($listview_id != ''){
			$this->db->select('*')->from('listview')->where(array('listview_id' => $listview_id, 'deleted' => 0));
		}else{
			$this->db->select('*')->from('listview')->where(array('module_id' => $this->module_id, 'default' => 1, 'visible' => 1, 'deleted' => 0));
		}
		$listview = $this->db->get()->result();
		

		//set listview qry
		$this->db->select('*')->from('listviewcolumn_list')->where(array('listview_id' => $listview[0]->listview_id, 'deleted' => 0 ))->order_by('sequence');
		$columns = $this->db->get()->result();

		//include the key field
		$this->listview_qry = array($this->module_table.'.'.$this->key_field);
		$this->listview_column_names = array();
		$this->listview_columns = array();
		$this->search_columns = array();
		$ctr = 0;

		foreach($columns as $row){
			//get the field detail
			foreach($record_detail as $fieldgroup){
				if(isset($fieldgroup['fields'])){
					foreach($fieldgroup['fields'] as $field){
						if($row->field_id == $field['field_id']){
							//other info
							$other_info = array();
							$affix_dbprefix = true;
							if( $field['uitype_id'] == 3 ){
								$realfieldcolumn = $field['column'];
								//get picklist
								$picklist = $this->db->get_where('picklist', array('field_id' => $field['field_id']));
								$picklist = $picklist->result_array();
								if( $picklist[0]['picklist_type'] == "Table" ){
									//get actual values from table
									$field['column'] = $picklist[0]['picklist_name'];
									$field['table'] = 't'.$ctr;
									$affix_dbprefix = false;
								}
								else{
									$other_info['picklist_type'] = "Query";
									$id_column = $picklist[0]['picklist_name'].'_id';
									$name_column = $picklist[0]['picklist_name'];
									$picklist_table = $picklist[0]['picklist_table'];
									$picklist_type = $picklist[0]['picklist_type'];
									$picklistvalues = $this->db->query( str_replace('{dbprefix}', $this->db->dbprefix, $picklist_table) );
									$other_info['picklistvalues'] = array();
									foreach($picklistvalues->result() as $row_ctr => $val_row){
										$other_info['picklistvalues'][$row_ctr]['id'] = $val_row->$id_column;
										$other_info['picklistvalues'][$row_ctr]['value'] = $val_row->$name_column;
									}
								}
							}

							if( $field['uitype_id'] == 39 ){
								$realfieldcolumn = $field['column'];
								//get picklist
								$picklist = $this->db->get_where('field_autocomplete', array('field_id' => $field['field_id']));
								$picklist = $picklist->result_array();
								if( $picklist[0]['type'] == "Table" ){
									//get actual values from table
									$field['column'] = $picklist[0]['label'];
									$field['table'] = 't'.$ctr;
									$affix_dbprefix = false;
								}
								elseif($picklist[0]['type'] == "Query"){
									$other_info['type'] = "Query";
									$id_column = $picklist[0]['value'];
									$name_column = $picklist[0]['label'];
									$picklist_table = $picklist[0]['table'];
									$picklist_type = $picklist[0]['type'];
									$picklistvalues = $this->db->query( str_replace('{dbprefix}', $this->db->dbprefix, $picklist_table) );
									$other_info['picklistvalues'] = array();
									foreach($picklistvalues->result() as $row_ctr => $val_row){
										$other_info['picklistvalues'][$row_ctr]['id'] = $val_row->$id_column;
										$other_info['picklistvalues'][$row_ctr]['value'] = $val_row->$name_column;
									}
								}else{
									$other_info['type'] = $picklist[0]['type'];	
								}
							}

							if( $field['uitype_id'] == 13 ){
								$realfieldcolumn = $field['column'];
								//get related module field
								$this->db->select('a.module_id, a.column, field.table, module.key_field');
								$this->db->from('field_module_link a');
								$this->db->join('module', 'module.module_id = a.module_id', 'left');
								$this->db->join('field', 'field.module_id = a.module_id', 'left');
								$this->db->where(array('a.field_id' => $field['field_id']));
								$relate_module = $this->db->get();
								if($relate_module->num_rows() > 0){
									$relate_module = $relate_module->row_array();
									$relate_column = $field['column'];
									$field['column'] = $relate_module['column'];
									$field['table'] = 't'.$ctr;
									$affix_dbprefix = false;
								}
								else{
									$relate_module = false;
								}
							}

							//check if multiple column
							if(strpos($field['column'], ',') && $field['uitype_id'] != 3 && $field['uitype_id'] != 39){
								$colum_lists = explode( ',', $field['column'] );
								$colum_list = array();
								foreach($colum_lists as $col_index => $column){
									$colum_list[$col_index] = $field['table'].'.'.$column;
								}
								$concat = 'CONCAT( '. implode(", ' ',", $colum_list) .' ) as '.$field['table'].implode('', $colum_lists);
								$this->listview_qry[] = $concat;
								$this->listview_columns[] = array("name" =>  $field['table'].implode('', $colum_lists), "width" => $row->width, "align" => $row->alignment, 'encrypt' => $field['encrypt']);
								$this->search_columns[] = array(
									'column' => 'CONCAT( '. implode(", ' ',", $colum_list) .' )',
									'jq_index' => $field['table'].implode('', $colum_lists)
								);
							}
							else if( $field['uitype_id'] == 24 ){
								if($row->sort == 1) $this->default_sort_col[] = $field['table'].'.'.$field['column'].'_from '.$row->sort_direction;
								$fldtable = $this->db->dbprefix.$field['table'].'.';
								$this->listview_qry[] = 'CONCAT('. $fldtable.$field['column'].'_from, " to ", '. $fldtable.$field['column'].'_to) AS '. $field['column'].'_from';
								
								$this->listview_columns[] = array("name" =>  $field['table'].'.'.$field['column'].'_from', "width" => $row->width, "align" => $row->alignment, 'encrypt' => $field['encrypt']);
								$this->search_columns[] = array(
									'column' => 'CONCAT('. $fldtable.$field['column'].'_from, " to ", '. $fldtable.$field['column'].'_to)',
									'jq_index' => $field['table'].'.'.$field['column']
								);
							}
							else if( $field['uitype_id'] == 40 ){
								if($row->sort == 1) $this->default_sort_col[] = $field['table'].'.'.$field['column'].'_from '.$row->sort_direction;
								$fldtable = $this->db->dbprefix.$field['table'].'.';
								$this->listview_qry[] = 'CONCAT(DATE_FORMAT('. $fldtable.$field['column'].'_from, \'%b %d, %Y %h:%i %p\'), " to ", DATE_FORMAT('. $fldtable.$field['column'].'_to, \'%b %d, %Y %h:%i %p\') ) AS '. $field['column'].'_from';
								
								$this->listview_columns[] = array("name" =>  $field['table'].'.'.$field['column'].'_from', "width" => $row->width, "align" => $row->alignment, 'encrypt' => $field['encrypt']);
								$this->search_columns[] = array(
									'column' => 'CONCAT('. $fldtable.$field['column'].'_from, " to ", '. $fldtable.$field['column'].'_to)',
									'jq_index' => $field['table'].'.'.$field['column']
								);
							}
							else if( $field['uitype_id'] == 35 ){
								$fldtable = $this->db->dbprefix.$field['table'].'.';
								$this->listview_qry[] = 'CONCAT(FORMAT('. $fldtable.$field['column'].'_from, 2), " to ", FORMAT('. $fldtable.$field['column'].'_to,2)) AS '. $field['column'];
								$this->listview_columns[] = array("name" =>  $field['table'].'.'.$field['column'], "width" => $row->width, "align" => $row->alignment, 'encrypt' => $field['encrypt']);
								$this->search_columns[] = array(
									'column' => 'CONCAT('. $fldtable.$field['column'].'_from, " to ", '. $fldtable.$field['column'].'_to)',
									'jq_index' => $field['table'].'.'.$field['column']
								);
							}
							else if( $field['uitype_id'] == 26 || $field['uitype_id'] == 38){
								$this->listview_qry[] = 'CONCAT(DATE_FORMAT('. $field['column'].'_start, \'%I:%i %p\'), " to ", DATE_FORMAT('. $field['column'].'_end, \'%I:%i %p\') ) AS '. $field['column'];
								$this->listview_columns[] = array("name" =>  $field['table'].'.'.$field['column'], "width" => $row->width, "align" => $row->alignment, 'encrypt' => $field['encrypt']);
								$this->search_columns[] = array(
									'column' => 'CONCAT('. $field['column'].'_start, " to ", '. $field['column'].'_end)',
									'jq_index' => $field['table'].'.'.$field['column']
								);
							}
							else if( $field['uitype_id'] == 3 ){
								$this->listview_qry[] = $field['table'].'.'.$field['column']. ' AS '.$field['table'].$field['column'];
								if($row->sort == 1) $this->default_sort_col[] = $field['table'].$field['column'].' '.$row->sort_direction;
								$this->listview_columns[] = array("name" =>  $field['table'].$field['column'], "width" => $row->width, "align" => $row->alignment, 'encrypt' => $field['encrypt']);
								if( $affix_dbprefix ){
									$this->search_columns[] = array(
										'column' => $this->db->dbprefix.$field['table'].'.'.$field['column'],
										'jq_index' => $field['table'].'.'.$field['column']
									);
								}
								else{
									$this->search_columns[] = array(
										'column' => $field['table'].'.'.$field['column'],
										'jq_index' => $field['table'].'.'.$field['column']
									);
								}
							}
							else if( $field['uitype_id'] == 39 && $picklist[0]['type'] == "Table" ){
								$colum_lists = explode( ',', $field['column'] );
								$colum_list = array();
								foreach($colum_lists as $col_index => $column){
									$colum_list[$col_index] = $field['table'].'.'.$column;
								}
								$concat = 'CONCAT( '. implode(", ' ',", $colum_list) .' ) as '.$field['table'].implode('', $colum_lists);
								$this->listview_qry[] = $concat;
								$this->listview_columns[] = array("name" =>  $field['table'].implode('', $colum_lists), "width" => $row->width, "align" => $row->alignment, 'encrypt' => $field['encrypt']);
								$this->search_columns[] = array(
									'column' => 'CONCAT( '. implode(", ' ',", $colum_list) .' )',
									'jq_index' => $field['table'].'.'.$field['column']
								);
							}
							else{
								$this->listview_qry[] = $field['table'].'.'.$field['column'];
								if($row->sort == 1) $this->default_sort_col[] = $field['table'].'.'.$field['column'].' '.$row->sort_direction;
								$this->listview_columns[] = array("name" =>  $field['table'].'.'.$field['column'], "width" => $row->width, "align" => $row->alignment, 'encrypt' => $field['encrypt']);
								if( $affix_dbprefix ){
									$this->search_columns[] = array(
										'column' => $this->db->dbprefix.$field['table'].'.'.$field['column'],
										'jq_index' => $field['table'].'.'.$field['column']
									);
								}
								else{
									$this->search_columns[] = array(
										'column' => $field['table'].'.'.$field['column'],
										'jq_index' => $field['table'].'.'.$field['column']
									);
								}
							}

							$this->listview_fields[] = array(
								'field_id' => $field['field_id'],
								'uitype_id' => $field['uitype_id'],
								'datatype' => explode( '~', $field['datatype'] ),
								'encrypt' => $field['encrypt'],
								'other_info' => $other_info
							);

							$this->listview_column_names[] = $field['fieldlabel'];

							if ( $field['table'] != $this->module_table || in_array($field['uitype_id'], array(3, 13, 39)) ){
								if( $field['uitype_id'] == 3 && $picklist[0]['picklist_type'] == "Table"){
									 $field['column'] .= '_id';
									 $field['table'] = $picklist[0]['picklist_table'];
									 $this->related_table[$field['table'].' t'.$ctr] = array( $field['column'], isset($realfieldcolumn) ? $realfieldcolumn :  $field['column'] );
								}

								if( $field['uitype_id'] == 13 && $relate_module ){
									$field['column'] = $relate_module['key_field'];
									$field['table'] = $relate_module['table'];
									$this->related_table[$field['table'].' t'.$ctr] = array( $field['column'], $relate_column );
								}

								if( $field['uitype_id'] == 39 && $picklist[0]['type'] == "Table"){
									 $field['column'] .= '_id';
									 $field['table'] = $picklist[0]['table'];
									 $this->related_table[$field['table'].' t'.$ctr] = array( $picklist[0]['value'] , isset($realfieldcolumn) ? $realfieldcolumn :  $field['column'] );
								}
							}
						}
					}
				}
			}
			$ctr++;
		}
		$this->listview_qry = implode(',', $this->listview_qry);
		//add actions column
		if( $this->show_action_column && $view_actions ){
			$this->listview_column_names[] = "Actions";
			$width = $this->action_column_width == "" ? '100' : $this->action_column_width;
			$align = $this->action_column_align == "" ? 'center' : $this->action_column_align;
			$this->listview_columns['action'] = array(
				"name" => "action",
				"align" => $align,
				"width" => $width,
				"sortable" => 'false',
				"classes" => "td-action"
			);
		}
	}

	function _set_search_all_query()
	{
		$value =  $this->input->post('searchString');
		$search_string = array();
		foreach($this->search_columns as $search)
		{
			$column = strtolower( $search['column'] );
			if(sizeof(explode(' as ', $column)) > 1){
				$as_part = explode(' as ', $column);
				$search['column'] = strtolower( trim( $as_part[0] ) );
			}
			$search_string[] = $search['column'] . ' LIKE "%'. $value .'%"' ;
		}
		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}

	function _set_specific_search_query()
	{
		$field = $this->input->post('searchField');
		$operator =  $this->input->post('searchOper');
		$value =  $this->input->post('searchString');

		foreach( $this->search_columns as $search )
		{
			if($search['jq_index'] == $field) $field = $search['column'];
		}

		$field = strtolower( $field );
		if(sizeof(explode(' as ', $field)) > 1){
			$as_part = explode(' as ', $field);
			$field = strtolower( trim( $as_part[0] ) );
		}

		switch ($operator) {
			case 'eq':
				return $field . ' = "'.$value.'"';
				break;
			case 'ne':
				return $field . ' != "'.$value.'"';
				break;
			case 'lt':
				return $field . ' < "'.$value.'"';
				break;
			case 'le':
				return $field . ' <= "'.$value.'"';
				break;
			case 'gt':
				return $field . ' > "'.$value.'"';
				break;
			case 'ge':
				return $field . ' >= "'.$value.'"';
				break;
			case 'bw':
				return $field . ' REGEXP "^'. $value .'"';
				break;
			case 'bn':
				return $field . ' NOT REGEXP "^'. $value .'"';
				break;
			case 'in':
				return $field . ' IN ('. $value .')';
				break;
			case 'ni':
				return $field . ' NOT IN ('. $value .')';
				break;
			case 'ew':
				return $field . ' LIKE "%'. $value  .'"';
				break;
			case 'en':
				return $field . ' NOT LIKE "%'. $value  .'"';
				break;
			case 'cn':
				return $field . ' LIKE "%'. $value .'%"';
				break;
			case 'nc':
				return $field . ' NOT LIKE "%'. $value .'%"';
				break;
			default:
				return $field . ' LIKE %'. $value .'%';
		}
	}

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
                            
        if ($this->user_access[$this->module_id]['add']) {
            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>".$addtext."</span></a></div>";
        }
         
        if ($this->user_access[$this->module_id]['delete']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        }

        if ( get_export_options( $this->module_id ) ) {
            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
            $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
        }        
        
        $buttons .= "</div>";
                
		return $buttons;
	}

	function _listview_in_boxy_grid_buttons()
	{
		$buttons = '<div class="icon-label-group">';
		
		if ($this->user_access[$this->module_id]['add']) {
			$buttons .= '<div class="icon-label"><a class="icon-16-add" href="javascript:void(0)" onclick="quick_add(\''.$this->module_link.'\', \''.$this->input->post('fieldname').'\', \''. $this->input->post('column') .'\', \''. $this->input->post('fmlinkctr') .'\')"><span>New</span></a></div>';
		}

		$buttons .= '</div>';
		return $buttons;
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
				
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        if ($this->user_access[$this->module_id]['delete']) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}

	function _default_field_module_link_actions($keyfield_id = 0, $container = '', $fmlinkctr = 0, $row_data = array())
	{
		if( $this->user_access[$this->module_id]['add'] == 1 ){
			$actions = '<span class="icon-group"><a class="icon-button icon-16-add" tooltip="Add" href="javascript:void(0)" onclick="addRelatedModule(\''.$this->input->post('fieldname').'\', \''. $keyfield_id .'\', \''. $this->related_module_add_column .'\', \''. $container .'\', \''. $fmlinkctr .'\')"></a></span>';
			return $actions;
		}
		return '';
	}

	/* END List View Functions */

	/* START Global Record/Module Actions */
	function _record_exist( $record_id = 0 )
	{
		$fields = $this->db->list_fields($this->module_table);

		if(in_array('sensitivity', $fields)){
			$this->db->select( $this->key_field.', sensitivity' );
		}
		else{
			$this->db->select( $this->key_field.", 1 as sensitivity", false );
		}

		$this->db->from( $this->module_table );
		$this->db->where( $this->module_table.'.'.$this->key_field." = '".$record_id."'" );
		
		$details = $this->db->get();
		if( $details->num_rows() == 1 ){
			if( ($this->sensitivity_filter && !isset($this->sensitivity[$this->module_id]) ) || ($this->sensitivity_filter && isset($this->sensitivity[$this->module_id]) && !in_array($details->row()->sensitivity, $this->sensitivity[$this->module_id] ))){
				$record->exist = false;
				$record->error_message = "Record Confidentiality";
				$record->error_message2 = "You are not allowed to view the requested record. Please contact the system administrator.";
			}
			else{
				$record->exist = true;
				$record->error_message = "";
			}
		}
		else if( $details->num_rows() ==  0){
			$record->exist = false;
			$record->error_message = "No record was found!";
			$record->error_message2 = "The record you are trying to access is not existing. Please contact the system administrator.";
		}
		else{
			$record->exist = false;
			$record->error_message = "Inconsistent data found!";
			$record->error_message2 = "The record you are trying to access is inconsistent. Please contact the system administrator.";
		}

		return $record;
	}

	function detail()
	{
        if( $this->extended ){
        	if(  !isset($_POST['record_id']) && $this->uri->rsegment(4) ){
        		$_POST['record_id'] = $this->uri->rsegment(4);
        	}
        	else{
        		if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ){ 
        			$_POST['record_id'] = $this->uri->rsegment(3);
        		}
        	}
        }
        else{
        	if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
        }
        
		
		if(!$this->input->post( 'record_id' )){
			$this->session->set_flashdata( 'flashdata', 'Insufficient data supplied!<br/>Please contact the System Administrator.' );
			redirect( base_url().$this->module_link );
		}
		
		if( $this->user_access[$this->module_id]['view'] != 1 ){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the view action! Please contact the System Administrator.');
			redirect( base_url() . $this->module_link );
		}
	
			$this->load->model( 'uitype_detail' );
		$check_record = $this->_record_exist( $this->input->post( 'record_id' ) );			
		if( $check_record->exist ){
			$data['fieldgroups'] = $this->_record_detail( $this->input->post('record_id') );				
			$this->key_field_val = $this->input->post('record_id');
		}
		else{
			$data['error'] = $check_record->error_message;
			$data['error2'] = $check_record->error_message2;
		}
		$this->load->vars( $data );
	}

	function edit()
	{
		if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
		
		if( !$this->input->post( 'record_id' ) ){
			$this->session->set_flashdata( 'flashdata', 'Insufficient data supplied!<br/>Please contact the System Administrator.' );
			redirect( base_url().$this->module_link );
		}
		
		if( $this->input->post( 'record_id' ) == "-1" && $this->user_access[$this->module_id]['add'] != 1 ){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the add action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
		
		if( $this->input->post( 'record_id' ) != "-1" && $this->user_access[$this->module_id]['edit'] != 1 ){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
		
		$this->load->model( 'uitype_edit' );
		$this->key_field_val = $this->input->post( 'record_id' );
		if($this->key_field_val != '-1') $check_record = $this->_record_exist( $this->input->post( 'record_id' ) );
		if( (isset($check_record) && $check_record->exist) || $this->key_field_val == "-1" ){
			$data['fieldgroups'] = $this->_record_detail( $this->input->post( 'record_id' ) );
			$this->session->set_userdata('before_edit', $data['fieldgroups']);
		}
		else{
			$data['error'] = $check_record->error_message;
			$data['error2'] = $check_record->error_message2;
		}

		if( $this->input->post('duplicate') ) $data['duplicate'] = TRUE;
		
		$this->load->vars($data);
	}

	function quick_edit( $customview = "" )
	{
		if( IS_AJAX ){
			$response->msg = "";
			if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
			if( $this->input->post( 'record_id' ) ){
				$dir = 'modules';
				if (CLIENT_DIR != ''){
					if(file_exists( 'lib/modules/client/'.CLIENT_DIR.'/'.$this->module_link.'.js' ) ){
						$dir = 'modules/client/'.CLIENT_DIR.'';
					}
				}

				if( ($this->input->post( 'record_id' ) == "-1" && $this->user_access[$this->module_id]['add'] == 1) || ($this->input->post( 'record_id' ) != "-1" && $this->user_access[$this->module_id]['edit'] == 1) ){
					$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/'.$dir.'/'.$this->module_link.'.js"></script>';
					$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/jquery/jquery.maskedinput-1.3.min.js"></script>';
					$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';

					$data['module'] = $this->module;
					$data['module_link'] = $this->module_link;
					$data['fmlinkctr'] = $this->input->post( 'fmlinkctr' );

					$this->load->model( 'uitype_edit' );
					$data['fieldgroups'] = $this->_record_detail( $this->input->post('record_id' ), true);

					$data['show_wizard_control'] = false;
					if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
						$data['show_wizard_control'] = true;
						$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
					}

					//other views to load
					$data['views'] = array();
					
					//load the final view
					$this->load->vars($data);
					if( isset($_POST['mode']) && $_POST['mode'] == 'copy' ) $_POST['record_id'] = -1;
					$response->quickedit_form = $this->load->view( $this->userinfo['rtheme']. $customview ."/quickedit" , "", true );
				}
				else{
					$response->msg = "You dont have sufficient privilege to execute the action! Please contact the System Administrator.";
					$response->msg_type = 'attention';	
				}
			}
			else{
				$response->msg = "Insufficient data supplied.";
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

	function _record_detail( $record_id = 0, $quick_edit_flag = false )
	{
		//get field groups
		$this->db->order_by( 'sequence' );
		$fieldgroups = $this->db->get_where( 'fieldgroup', array( "module_id" => $this->module_id, "visible" => 1, "deleted" => 0 ) );
		
		if( $fieldgroups->num_rows() > 0 ){
			$fieldgroups = $fieldgroups->result_array();
			$table_fields = array();
			foreach( $fieldgroups as $fgroup_index => $fg_detail ){				
				//get the field set for field group
				$fields = $this->_get_fieldgroup_fields( $fg_detail['fieldgroup_id'], $quick_edit_flag );
				if( $fields->num_rows() > 0 ){
					$select = array();

					$fields = $fields->result_array();
					foreach( $fields as $field ){
						if (!array_key_exists($field['table'], $table_fields)) {
							$table_fields[$field['table']] = $this->db->list_fields($field['table']);
						}

						if( $field['uitype_id'] == 24 ||  $field['uitype_id'] == 35 || $field['uitype_id'] == 40){
							$select[] = $field['table'].'.'.$field['column'].'_from';
							$select[] = $field['table'].'.'.$field['column'].'_to';
						}
						else if( $field['uitype_id'] == 25 ){
							//do nothing, exclude this field from query
						}
						else if( $field['uitype_id'] == 26 || $field['uitype_id'] == 38){
							$select[] = $field['table'].'.'.$field['column'].'_start';
							$select[] = $field['table'].'.'.$field['column'].'_end';
						}
						else if(in_array($field['column'], $table_fields[$field['table']])) {
							$select[] = $field['table'].'.'.$field['column'];
						}

						//set related tables
						if ( $field['table'] != $this->module_table ) $this->related_table[$field['table']] = array( $this->key_field, $this->key_field );
					}
					
					if( $record_id != "-1" ){
						//set query for detail/field values
						$this->db->select( implode( ',', $select ) );
						$this->_set_left_join();
						$this->db->from( $this->module_table );
						$this->db->where( $this->module_table.'.'.$this->key_field." = '".$record_id."'" );
						$details = $this->db->get();
						
						if ($this->db->_error_message()) {
							show_error($this->db->_error_message() . '<br />' . $this->db->last_query());
							$this->session->set_flashdata('flashdata', $this->db->_error_message());
							redirect(base_url());
						}

						//include standard_custom_view with the condition because it will used multiple records of data : tirso
						if( $details->num_rows() == 1 && $fg_detail['standard_custom_view'] == 0) {
							$details = $details->result_array();

							//finalise array of fields
							$field_array = array();
							foreach( $fields as $field_index => $field ){
								if( $field['encrypt'] == 1 ){
									//decrypt the value
									$this->load->library('encrypt');
									$details[0][$field['column']] = $this->encrypt->decode( $details[0][$field['column']] );
								}
								
								//handle value of uitype
								if( $field['uitype_id'] == 24 ){
									$fields[$field_index]['value'] = "";
									if($details[0][$field['column'].'_from'] == '0000-00-00') $details[0][$field['column'].'_from'] = '';
									if($details[0][$field['column'].'_to'] == '0000-00-00') $details[0][$field['column'].'_to'] = '';
									if(!empty($details[0][$field['column'].'_from']) && !empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= date('d-M-Y', strtotime($details[0][$field['column'].'_from'])).' to '.date('d-M-Y', strtotime($details[0][$field['column'].'_to']));
									}
									else if(!empty($details[0][$field['column'].'_from']) && empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= date('d-M-Y', strtotime($details[0][$field['column'].'_from'])).' to ';
									}
									else if(empty($details[0][$field['column'].'_from']) && !empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= ' to '.date('d-M-Y', strtotime($details[0][$field['column'].'_to']));
									}
								}
								else if( $field['uitype_id'] == 40 ){
									if($details[0][$field['column'].'_from'] == '0000-00-00 00:00:00') $details[0][$field['column'].'_from'] = '';
									if($details[0][$field['column'].'_to'] == '0000-00-00 00:00:00') $details[0][$field['column'].'_to'] = '';
									if(!empty($details[0][$field['column'].'_from']) && !empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= date('d-M-Y h:i:s a', strtotime($details[0][$field['column'].'_from'])).' to '.date('d-M-Y h:i:s a', strtotime($details[0][$field['column'].'_to']));
									}
									else if(!empty($details[0][$field['column'].'_from']) && empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= date('d-M-Y h:i:s a', strtotime($details[0][$field['column'].'_from'])).' to ';
									}
									else if(empty($details[0][$field['column'].'_from']) && !empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= ' to '.date('d-M-Y h:i:s a', strtotime($details[0][$field['column'].'_to']));
									}
								}
								else if( $field['uitype_id'] == 25 ){
									$fields[$field_index]['value'] = "";
								}
								else if( $field['uitype_id'] == 26){
									$fields[$field_index]['value'] = $details[0][$field['column'].'_start'].' to '.$details[0][$field['column'].'_end'];
								}
								else if($field['uitype_id'] == 38){
									$fields[$field_index]['value'] = date('h:i A' ,strtotime($details[0][$field['column'].'_start']) ) .' to '.date('h:i A' ,strtotime($details[0][$field['column'].'_end']) );
								}
								else if( $field['uitype_id'] == 35 ){
									$fields[$field_index]['value'] = number_format( $details[0][$field['column'].'_from'], 2, '.', ',').' to '.number_format( $details[0][$field['column'].'_to'], 2, '.', ',');
								}
								else{
									//default uitype
									$fields[$field_index]['value'] = $details[0][$field['column']];
								}
							}
							$fieldgroups[$fgroup_index]['fields'] = $fields;
						}
						//standard_custom_view, it will used multiple records of data : tirso
						elseif( $details->num_rows() >= 1 && $fg_detail['standard_custom_view'] == 1) {
							$details = $details->result_array();

							//finalise array of fields
							$field_array = array();
							foreach( $fields as $field_index => $field ){
								if( $field['encrypt'] == 1 ){
									//decrypt the value
									$this->load->library('encrypt');
									$details[0][$field['column']] = $this->encrypt->decode( $details[0][$field['column']] );
								}
								
								//handle value of uitype
								if( $field['uitype_id'] == 24 ){
									$fields[$field_index]['value'] = "";
									if($details[0][$field['column'].'_from'] == '0000-00-00') $details[0][$field['column'].'_from'] = '';
									if($details[0][$field['column'].'_to'] == '0000-00-00') $details[0][$field['column'].'_to'] = '';
									if(!empty($details[0][$field['column'].'_from']) && !empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= date('d-M-Y', strtotime($details[0][$field['column'].'_from'])).' to '.date('d-M-Y', strtotime($details[0][$field['column'].'_to']));
									}
									else if(!empty($details[0][$field['column'].'_from']) && empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= date('d-M-Y', strtotime($details[0][$field['column'].'_from'])).' to ';
									}
									else if(empty($details[0][$field['column'].'_from']) && !empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= ' to '.date('d-M-Y', strtotime($details[0][$field['column'].'_to']));
									}
								}
								else if( $field['uitype_id'] == 40 ){
									if($details[0][$field['column'].'_from'] == '0000-00-00 00:00:00') $details[0][$field['column'].'_from'] = '';
									if($details[0][$field['column'].'_to'] == '0000-00-00 00:00:00') $details[0][$field['column'].'_to'] = '';
									if(!empty($details[0][$field['column'].'_from']) && !empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= date('d-M-Y h:i:s a', strtotime($details[0][$field['column'].'_from'])).' to '.date('d-M-Y h:i:s a', strtotime($details[0][$field['column'].'_to']));
									}
									else if(!empty($details[0][$field['column'].'_from']) && empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= date('d-M-Y h:i:s a', strtotime($details[0][$field['column'].'_from'])).' to ';
									}
									else if(empty($details[0][$field['column'].'_from']) && !empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= ' to '.date('d-M-Y h:i:s a', strtotime($details[0][$field['column'].'_to']));
									}
								}
								else if( $field['uitype_id'] == 25 ){
									$fields[$field_index]['value'] = "";
								}
								else if( $field['uitype_id'] == 26){
									$fields[$field_index]['value'] = $details[0][$field['column'].'_start'].' to '.$details[0][$field['column'].'_end'];
								}
								else if($field['uitype_id'] == 38){
									$fields[$field_index]['value'] = date('h:i A' ,strtotime($details[0][$field['column'].'_start']) ) .' to '.date('h:i A' ,strtotime($details[0][$field['column'].'_end']) );
								}
								else if( $field['uitype_id'] == 35 ){
									$fields[$field_index]['value'] = number_format( $details[0][$field['column'].'_from'], 2, '.', ',').' to '.number_format( $details[0][$field['column'].'_to'], 2, '.', ',');
								}
								else{
									//default uitype
									$fields[$field_index]['value'] = $details[0][$field['column']];
								}
							}
							$fieldgroups[$fgroup_index]['fields'] = $fields;
						}						
					}
					else{
						foreach($fields as $field_index => $field){
							$fields[$field_index]['value'] = "";
							if( $this->input->post($fields[$field_index]['fieldname']) ){
								$fields[$field_index]['value'] = $this->input->post( $fields[$field_index]['fieldname'] );
							}

							if( $field['uitype_id'] == 24 ){
								if( $this->input->post($fields[$field_index]['fieldname'].'_from') ){
									$fields[$field_index]['value'] = date('d-M-Y', strtotime($this->input->post($fields[$field_index]['fieldname'].'_from')));
								}

								$fields[$field_index]['value'] .= ' to ';

								if( $this->input->post($fields[$field_index]['fieldname'].'_to') ){
									$fields[$field_index]['value'] .= date('d-M-Y', strtotime($this->input->post($fields[$field_index]['fieldname'].'_to')));
								}
							}
						}
						$fieldgroups[$fgroup_index]['fields'] = $fields;
						
					}					
				}
			}
			return $fieldgroups;
		}
		else{
			return false;
		}
	}

	function ajax_save()
	{

		if( IS_AJAX ) {
			$response->msg = "";
			if ($this->input->post('record_id') == '-1') {
				$allow = $this->user_access[$this->module_id]['add'];
			} else {
				$allow = $this->user_access[$this->module_id]['edit'];
			}

			if( $allow ) { 
				if($_POST){
					$this->load->library('form_validation');
					$validate_fields = array();

					$quick_edit_flag = ( ( $this->input->post('quick_edit_flag') && $this->input->post('quick_edit_flag') == "true" ) ? true : false );

					foreach($_POST as $fieldname => $fieldvalue){
						$post[$fieldname] = $fieldvalue;
						//check if any of the post fields is actually also the record field
						if( $fieldname == $this->key_field ) $this->key_field_val = $post[$this->key_field] = $row[$this->key_field] = $fieldvalue;
					}
					
					//set key field if adding new record
					if( strcmp($post['record_id'], '-1') == 0 ){
						$this->key_field_val = "-1";
					}
					else{
						$this->key_field_val = $post[$this->key_field] = $post['record_id'];
						unset( $post['record_id'] );
					}

					// start insert/update
					//get the fields of main table
					$this->db->select('field.fieldname, field.column, field.uitype_id, field.datatype, field.fieldlabel, field.encrypt, field.table');
					$this->db->join('fieldgroup', 'fieldgroup.fieldgroup_id = field.fieldgroup_id');
					$where = array( 'field.module_id' => $this->module_id, "field.table" => $this->module_table, 'field.deleted' => 0, 'fieldgroup.standard_custom_view' => 0, 'fieldgroup.deleted' => 0 );
					if($quick_edit_flag) $where['quick_edit'] = 1;
					$this->db->where( $where );
					$this->db->from('field');
					$fieldset = $this->db->get();
					$fieldset = $fieldset->result_array();
					$row = array();
					
					if( $this->key_field_val != "-1" ) $post[$this->key_field] = $row[$this->key_field] = $this->key_field_val;

					foreach($fieldset as $field_index => $field){
						if( isset($post[$field['fieldname']]) || ($field['uitype_id'] == 31 && isset($post[$field['fieldname'] . '_mm']) && isset($post[$field['fieldname'] . '_hh'])) || ( in_array($field['uitype_id'], array(24,35,37))  && isset($post[$field['fieldname'].'_from']) || isset($post[$field['fieldname'].'_to']) ) || ($field['uitype_id'] == 26 && isset($post[$field['fieldname'].'_start_hh']) || isset($post[$field['fieldname'].'_start_mm'])|| isset($post[$field['fieldname'].'_end_hh'])|| isset($post[$field['fieldname'].'_end_mm']) ) || ($field['uitype_id'] == 38 && isset($post[$field['fieldname'].'_start']) || isset($post[$field['fieldname'].'_end']) ) ){
							if($field['uitype_id'] == 21 && !empty($post[$field['fieldname']])){
								$list =  explode( ',', $post[$field['fieldname']]);
								asort($list);
								$row[$field['column']] = implode(',', $list);
							}
							elseif($field['uitype_id'] == 5){
								//format date field to something db can read
								if($post[$field['fieldname']] != ""){
									$temp = explode('/', $post[$field['fieldname']]);
									$temp = $temp[2].'-'.$temp[0].'-'.$temp[1];
									$row[$field['column']] = $temp;
								}
								else{
									$row[$field['column']] = '';
								}								
							}
							elseif($field['uitype_id'] == 10){
								//handle password field
								if($post[$field['fieldname']] != ""){
									//set password md5
									$row[$field['column']] = md5($post[$field['fieldname']]);
								}
							}
							else if($field['uitype_id'] == 19){
								//handle days array field
								$row[$field['column']] = sizeof($post[$field['fieldname']]) > 0 ? serialize($post[$field['fieldname']]) : "";
							}
							else if($field['uitype_id'] == 24){
								//format date from and date to field to something db can read
								if($post[$field['fieldname'].'_from'] != ""){
									$row[$field['column'].'_from'] = date('Y-m-d', strtotime($post[$field['fieldname'].'_from']));
								}
								else{
									$row[$field['column'].'_from'] = "";
								}

								if($post[$field['fieldname'].'_to'] != ""){
									$row[$field['column'].'_to'] = date('Y-m-d', strtotime($post[$field['fieldname'].'_to']));
								}
								else{
									$row[$field['column'].'_to'] = "";
								}
							}
							else if($field['uitype_id'] == 40){
								//format date from and date to field to something db can read
								if($post[$field['fieldname'].'_from'] != ""){
									$row[$field['column'].'_from'] = date('Y-m-d H:i:s', strtotime($post[$field['fieldname'].'_from']));
								}
								else{
									$row[$field['column'].'_from'] = "";
								}

								if($post[$field['fieldname'].'_to'] != ""){
									$row[$field['column'].'_to'] = date('Y-m-d H:i:s', strtotime($post[$field['fieldname'].'_to']));
								}
								else{
									$row[$field['column'].'_to'] = "";
								}
							}
							else if($field['uitype_id'] == 26){
								//format time start and time to field to something db can read
								$starthh = $post[$field['fieldname'].'_start_hh'];
								$startmm = $post[$field['fieldname'].'_start_mm'];

								$endhh = $post[$field['fieldname'].'_end_hh'];
								$endmm = $post[$field['fieldname'].'_end_mm'];
								if($starthh != "" && $startmm != ""){
									$temp = $starthh.':'.$startmm;
									$row[$field['column'].'_start'] = $temp;
								}
								else{
									$row[$field['column'].'_start'] = "";
								}

								if($endhh != "" && $endmm != ""){
									$temp = $endhh.':'.$endmm;
									$row[$field['column'].'_end'] = $temp;
								}
								else{
									$row[$field['column'].'_end'] = "";
								}
							}
							else if ($field['uitype_id'] == 31) {
								$hh = $post[$field['fieldname'].'_hh'];
								$mm = $post[$field['fieldname'].'_mm'];
								if($hh != "" && $mm != ""){
									$temp = $hh.':'.$mm;
									$row[$field['column']] = $temp;
								}
								else{
									$row[$field['column']] = "";
								}
							}			
							else if ($field['uitype_id'] == 32) {	
							 	 // Convert to 24hour mysql format.
							 	 if ($post[$field['fieldname']] == '') {
							 	 	$row[$field['column']] = '0000-00-00 00:00:00';
							 	 } else {
								 	$row[$field['column']] = date('Y-m-d H:i:s', strtotime($post[$field['fieldname']]));
							 	 }
							}
							else if ($field['uitype_id'] == 33) {	
							 	 // Convert to 24hour mysql format.
							 	 if ($post[$field['fieldname']] == '') {
							 	 	$row[$field['column']] = '00:00:00';
							 	 } else {
								 	$row[$field['column']] = date('H:i:s', strtotime($post[$field['fieldname']]));
							 	 }
							}  
							else if ($field['uitype_id'] == 35) {	
							 	 // number range
								 $row[$field['column'].'_from'] = str_replace(",", "", $post[$field['fieldname'].'_from']);
								 $row[$field['column'].'_to'] = str_replace(",", "", $post[$field['fieldname'].'_to']);
							}    
							else if ($field['uitype_id'] == 37) {	
							 	 // Prepend "00:" to convert to mm:ss to hh:mm:ss format
							 	 $mmss = $post[$field['fieldname']];
								 $row[$field['column']] = date('Y-m-d H:i:s', strtotime("00:{$mmss}"));
							}
							else if($field['uitype_id'] == 38){
								$start = $post[$field['fieldname'].'_start'];
								$end = $post[$field['fieldname'].'_end'];

								$row[$field['column'].'_start'] = ($start != "") ? date('H:i:s', strtotime($start)) : "";
								$row[$field['column'].'_end'] = ($end != "") ? date('H:i:s', strtotime($end))  : "";
							}             
							else if($field['uitype_id'] == 39){
								if( is_array($this->input->post( $field['fieldname'])) ) {	
									$row[$field['column']] = implode(',', $post[$field['fieldname']]);
								} else {
									$row[$field['column']] = $post[$field['fieldname']];
								}
							}
							else{
								//handle floats and integers, remove commas
								$numeric = false;
								$datatypes = explode('~', $field['datatype']);
								foreach($datatypes as $datatype){
									if($datatype == "I" || $datatype == "F") $numeric = true;
								}
								if( $numeric ) $post[$field['fieldname']] = str_replace(",", "", $post[$field['fieldname']]);
								$row[$field['column']] = $post[$field['fieldname']];
							}
						} else if (!isset($post[$field['fieldname']]) && $field['uitype_id'] == 30) {
							$row[$field['column']] = 0;
						}

						// Define server side validation. JMC
						$datatypes = explode('~', $field['datatype']);
						$type = array();
						if (!in_array($field['uitype_id'], array(38, 31, 35, 26, 24))) {
							foreach ($datatypes as $datatype) {
								switch ($datatype) {
									case 'M':
										$type[] = 'required';
										break;
									case 'E':
										$type[] = 'valid_email';
										break;
									case 'N':
										$type[] = 'numeric';
										break;	
									case 'UN':
										$type[] = 'callback_hdi_is_unique[' . json_encode(array('field' => $field, 'id' => $this->key_field_val, 'key_field' => $this->key_field)) . ']';
										break;
									default:								
										break;
								}
							}
						}

						if (sizeof( $type ) > 0 ) {
							$type = 'trim|' . implode('|', $type) . '|xss_clean';							
						} else {
							$type = 'trim|xss_clean';
						}
						
						if (is_array($this->input->post( $field['fieldname'] ))) {
							$field['fieldname'] = $field['fieldname'] . '[]';
						}

						if ($this->input->post($field['fieldname']) != '')
							$validate_fields[] = array('field' => $field['fieldname'], 'label' => $field['fieldlabel'], 'rules' => $type);
						
						//check if for encryption
						if( $field['encrypt'] == 1 && !empty( $row[$field['column']] ) ){
							$this->load->library('encrypt');
							$row[$field['column']] = $this->encrypt->encode( $row[$field['column']] );
						}
					}//end foreach fieldset
					
					$this->form_validation->set_rules($validate_fields);
					
					if (count($row) > 0 && ( sizeof( $validate_fields ) == 0 || $this->form_validation->run() ) ) {					
						// Check if an entry already exists
						$this->db->where($this->key_field, $this->key_field_val);
						$record = $this->db->get( $this->module_table );
						if( $record->num_rows() == 0){
							//new record
							//insert to main table
							$this->db->insert($this->module_table, $row);
							if( !isset( $post[$this->key_field] ) || $post[$this->key_field] == '-1' ) $post[$this->key_field] = $this->db->insert_id();
						}
						else{
							//record exist update it
							//update main table
							$this->db->update( $this->module_table, $row, array( $this->key_field => $post[$this->key_field] ) );
						}
						
						if( $this->db->_error_message() != "" ){
							if(  $response->msg == "" ) $response->msg = $this->db->_error_message();
							$response->msg_type = 'error';
						}
						else{
							$response->record_id = $post[$this->key_field];
							$this->key_field_val = $response->record_id;
						}
						// end insert/update

						if( $this->db->_error_message() == "" ){
							//handle other field saved in different table
							$validate_fields = array();
							foreach($this->related_table as $table => $key_field){		
								//get field set of table
								$this->db->select('field.fieldname, field.column, field.uitype_id, field.datatype, field.fieldlabel, field.encrypt');
								$this->db->join('fieldgroup', 'fieldgroup.fieldgroup_id = field.fieldgroup_id');
								$where = array( 'field.module_id' => $this->module_id, "field.table" => $table, 'field.deleted' => 0, 'fieldgroup.standard_custom_view' => 0, 'fieldgroup.deleted' => 0 );
								if($quick_edit_flag) $where['quick_edit'] = 1;
								$this->db->where( $where );
								$this->db->from('field');
								$fieldset = $this->db->get();
								$fieldset = $fieldset->result_array();
								$row = array($key_field => $post[$this->key_field]);

								foreach($fieldset as $field_index => $field){
									if( isset($post[$field['fieldname']]) || ($field['uitype_id'] == 31 && isset($post[$field['fieldname'] . '_mm']) && isset($post[$field['fieldname'] . '_hh'])) || ( in_array($field['uitype_id'], array(24,35))  && isset($post[$field['fieldname'].'_from']) || isset($post[$field['fieldname'].'_to']) ) || ($field['uitype_id'] == 26 && isset($post[$field['fieldname'].'_start_hh']) || isset($post[$field['fieldname'].'_start_mm'])|| isset($post[$field['fieldname'].'_end_hh'])|| isset($post[$field['fieldname'].'_end_mm']) ) ){
										if($field['uitype_id'] == 21 && !empty($post[$field['fieldname']])){
											$list =  explode( ',', $post[$field['fieldname']]);
											asort($list);
											$row[$field['column']] = implode(',', $list);
										}
										elseif($field['uitype_id'] == 5){
											//format date field to something db can read
											if($post[$field['fieldname']] != ""){
												$temp = explode('/', $post[$field['fieldname']]);
												$temp = $temp[2].'-'.$temp[0].'-'.$temp[1];
												$row[$field['column']] = $temp;
											}
											else{
												$row[$field['column']] = '';
											}
										}
										elseif($field['uitype_id'] == 10){
											//handle password field
											if($post[$field['fieldname']] != ""){
												//set password md5
												$row[$field['column']] = md5($post[$field['fieldname']]);
											}
										}
										else if($field['uitype_id'] == 19){
											//handle days array field
											$row[$field['column']] = sizeof($post[$field['fieldname']]) > 0 ? serialize($post[$field['fieldname']]) : "";
										}
										else if($field['uitype_id'] == 24){
											//format date from and date to field to something db can read
											if($post[$field['fieldname'].'_from'] != ""){
												$row[$field['column'].'_from'] = date('Y-m-d', strtotime($post[$field['fieldname'].'_from']));
											}
											else{
												$row[$field['column'].'_from'] = "";
											}

											if($post[$field['fieldname'].'_to'] != ""){
												$row[$field['column'].'_to'] = date('Y-m-d', strtotime($post[$field['fieldname'].'_to']));
											}
											else{
												$row[$field['column'].'_to'] = "";
											}
										}
										else if($field['uitype_id'] == 40){
											//format date from and date to field to something db can read
											if($post[$field['fieldname'].'_from'] != ""){
												$row[$field['column'].'_from'] = date('Y-m-d h:i:s a', strtotime($post[$field['fieldname'].'_from']));
											}
											else{
												$row[$field['column'].'_from'] = "";
											}

											if($post[$field['fieldname'].'_to'] != ""){
												$row[$field['column'].'_to'] = date('Y-m-d h:i:s a', strtotime($post[$field['fieldname'].'_to']));
											}
											else{
												$row[$field['column'].'_to'] = "";
											}
										}
										else if($field['uitype_id'] == 26){
											//format time start and time to field to something db can read
											$starthh = $post[$field['fieldname'].'_start_hh'];
											$startmm = $post[$field['fieldname'].'_start_mm'];
			
											$endhh = $post[$field['fieldname'].'_end_hh'];
											$endmm = $post[$field['fieldname'].'_end_mm'];
											if($starthh != "" && $startmm != ""){
												$temp = $starthh.':'.$startmm;
												$row[$field['column'].'_start'] = $temp;
											}
											else{
												$row[$field['column'].'_start'] = "";
											}
			
											if($endhh != "" && $endmm != ""){
												$temp = $endhh.':'.$endmm;
												$row[$field['column'].'_end'] = $temp;
											}
											else{
												$row[$field['column'].'_end'] = "";
											}
										}
										else if ($field['uitype_id'] == 31) {
											$hh = $post[$field['fieldname'].'_hh'];
											$mm = $post[$field['fieldname'].'_mm'];
			
											if($hh != "" && $mm != ""){
												$temp = $hh.':'.$mm;
												$row[$field['column']] = $temp;
											}
											else{
												$row[$field['column']] = "";
											}
										}
										else if ($field['uitype_id'] == 35) {	
											 // number range
											 $row[$field['column'].'_from'] = str_replace(",", "", $post[$field['fieldname'].'_from']);
											 $row[$field['column'].'_to'] = str_replace(",", "", $post[$field['fieldname'].'_to']);
										}
										else if($field['uitype_id'] == 38) {
											$start = $post[$field['fieldname'].'_start'];
											$end = $post[$field['fieldname'].'_end'];

											$row[$field['column'].'_start'] = ($start != "") ? $start : "";
											$row[$field['column'].'_end'] = ($end != "") ? $end : "";
										}                                                               
										else{
											//handle floats and integers, remove commas
											$numeric = false;
											$datatypes = explode('~', $field['datatype']);
											foreach($datatypes as $datatype){
												if($datatype == "I" || $datatype == "F") $numeric = true;
											}
											if( $numeric ) $post[$field['fieldname']] = str_replace(",", "", $post[$field['fieldname']]);
											$row[$field['column']] = $post[$field['fieldname']];
										}
									}

									// Define server side validation. JMC								
									$datatypes = explode('~', $field['datatype']);
									$type = array();
									
									if (!in_array($field['uitype_id'], array(38, 31, 35, 26, 24))) {									
										foreach ($datatypes as $datatype) {
											switch ($datatype) {
												case 'M':
													$type[] = 'required';
													break;
												case 'E':
													$type[] = 'valid_email';
													break;
												case 'N':
													$type[] = 'numeric';
													break;				
												default:								
													break;
											}									
										}
									}

									if (sizeof( $type ) > 0 ) {
										$type = 'trim|' . implode('|', $type) . '|xss_clean';							
									} else {
										$type = 'trim|xss_clean';
									}
									
									if( $this->input->post( $field['fieldname'] ) != '' ) {							
										if (is_array($this->input->post( $field['fieldname'] ))) {
											$field['fieldname'] = $field['fieldname'] . '[]';
										}

										$validate_fields[] = array('field' => $field['fieldname'], 'label' => $field['fieldlabel'], 'rules' => $type);
									}
									
									//check if for encryption
									if( $field['encrypt'] == 1 && !empty( $row[$field['column']] ) ){
										$this->load->library('encrypt');
										$row[$field['column']] = $this->encrypt->encode( $row[$field['column']] );
									}								
								}//end foreach fieldset

								$this->form_validation->reset_rules();
								
								$this->form_validation->set_rules($validate_fields);

								if ( sizeof( $validate_fields ) == 0 || $this->form_validation->run() ) {
									// Check if an entry already exists for this related table.j
									$this->db->where($key_field, $post[$this->key_field]);
									$record = $this->db->get( $table );

									if( $record->num_rows() == 0){
										//new record
										$this->db->insert($table, $row);
									}
									else{
										//exists, update record
										$this->db->where($key_field, $post[$this->key_field]);
										$this->db->update($table, $row);
									}
									
									if( $this->db->_error_message() != ""){
										$response->msg = $this->db->_error_message();
										$response->msg_type = 'error';
									}
									
									unset($row);
									
								} else {
									$response->msg = validation_errors();
									$response->msg_type = 'error';
									break;
								}
							}
						}

						if( $this->db->_error_message() == "" && $response->msg == "" ){
							if ($this->input->post('on_success') == "email"){
								$response->msg = 'Data has been successfully saved and sent.';
							}
							else{
								$response->msg = 'Data has been successfully saved.';
							}							
							$response->msg_type = 'success';
						}
						else{
							if(  $response->msg == "" ) $response->msg = $this->db->_error_message();
							$response->msg_type = 'error';
						}							
					} else {	
						//change to enable to work edit custom view
						if(validation_errors() != false){
							$response->msg = validation_errors();
							$response->msg_type = 'error';
						}
						else{
							$response->msg = 'Data has been successfully saved.';
							$response->msg_type = 'success';
						}
					}
				}
				else{
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'error';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}

			$this->set_message($response);
			$this->after_ajax_save();
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function hdi_is_unique($str, $field) {
		$p = json_decode($field);
		$new = false;

		if ($p->id != '-1') {
			$this->db->where($p->key_field, $p->id);
			$this->db->where('deleted', 0);
			
			$result = $this->db->get($p->field->table)->row();
		} else {
			$new = TRUE;
		}

		if ($new == TRUE || $result->{$p->field->column} != $str) {			
			$this->db->where($p->field->column, $str);
			$this->db->where('deleted', 0);
			$result = $this->db->get($p->field->table);

			if ($result && $result->num_rows() > 0) {
				$this->form_validation->set_message('hdi_is_unique', 'The %s field must be unique.');
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Place these abstractions so that the ajax_save response message can be altered by other controllers.
	 */
	protected function after_ajax_save() {
		
		$this->load->vars(array('json' => $this->get_message()));
		$this->load->view($this->userinfo['rtheme'].'/template/ajax');		
	}

	protected function get_message() {
		$response->msg 			= $this->get_msg_text();
		$response->msg_type 	= $this->get_msg_type();
		$response->record_id    = $this->record_id;
		$response->page_refresh = $this->page_refresh;

		return $response;
	}

	protected function set_message($object) {
		$this->set_msg_text($object->msg);
		$this->set_msg_type($object->msg_type);

		$this->page_refresh = isset( $object->page_refresh ) ? $object->page_refresh : false ;
		$this->record_id = $object->record_id;
	}

	protected function get_msg_text() {
		return $this->_msg;
	}

	protected function get_msg_type() {
		return $this->_msg_type;
	}

	public function set_msg_text($msg) {
		$this->_msg = $msg;
	}

	public function set_msg_type($msg_type) {
		$this->_msg_type = $msg_type;
	}

	function delete()
	{
		if( IS_AJAX ){
			if($this->user_access[$this->module_id]['delete'] == 1){
				if($this->input->post('record_id')){
					$record_id = explode(',', $this->input->post('record_id'));
					$this->db->where_in($this->key_field, $record_id);
					$this->db->update($this->module_table, array('deleted' => 1));
					if( $this->db->_error_message() == "" ){
						$response->msg = "Record(s) has been deleted.";
						$response->msg_type = 'success';
					}
					else{
						$response->msg = $this->db->_error_message();
						$response->msg_type = 'error';
					}
				}
				else{
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'attention';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	/* END Global Record/Module Actions */

	/* START Other Global Functions */
	function get_related_module()
	{
		if( IS_AJAX ){
			$response->msg = "";

			if($this->input->post('field_id')){
				$this->db->select('a.module_id, a.column, module.class_path, module.short_name');
				$this->db->from('field_module_link a');
				$this->db->join('module', 'module.module_id = a.module_id', 'left');
				$this->db->where(array('field_id' => $this->input->post('field_id')));
				$module = $this->db->get();
				if( $this->db->_error_message() == "" ){
					if($module->num_rows() > 0){
						$module = $module->row();
						$response->link = $module->class_path;
						$response->short_name = $module->short_name;
						$response->column = $module->column;
					}
				}
				else{
					$response->msg = $this->db->_error_message();
					$response->msg_type = 'error';
				}
			}
			else{
				$response->msg = "Insufficient data supplied.";
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

	function show_related_module()
	{
		if( IS_AJAX ){
			$data['container'] = $this->module.'-fmlink-container';
			$data['pager'] = $this->module.'-fmlink-pager';
			$data['fmlinkctr'] = $this->input->post('fmlinkctr');

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
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function file_upload()
	{
		if(IS_AJAX){
			$response->msg = "";

			if( $this->user_access[$this->module_id]['edit'] == 1 ){
				$this->db->insert('file_upload', $_POST);
				if( $this->db->_error_message() == "" ){
					$response->upload_id =  $this->db->insert_id();
				}
				else{
					$response->msg = $this->db->_error_message();
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function access_restricted()
	{	
		$this->load->view( $this->userinfo['rtheme']."/template/access_restricted" );	
	}

	function compare_date()
	{
		if( IS_AJAX ){
			$response->msg = "";
			
			$response = $this->hdicore->_compare_date( $this->input->post('date1'), $this->input->post('date2') );

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function whos_loggedin()
	{
		$response->user = $this->user;
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

	function webcam_snapshot(){
		$filename = date('YmdHis') . '.jpg';
		$result = file_put_contents( $filename, file_get_contents('php://input') );
		if (!$result) {
			print "ERROR: Failed to write data to $filename, check permissions\n";
			exit();
		}

		$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $filename;
		print "$url\n";
	}

	function set_footer_widget_state(){
		if(IS_AJAX){
			$response->msg = "";
			$this->load->model('portlet');
			$this->portlet->_update_user_config( 'footer_widget_state', base64_encode( serialize( $this->input->post('state') ) ), $this->user->user_id );

			if( $this->db->_error_message() != "" ){
				$response->msg = $this->db->_error_message();
				$response->msg_type = "error";
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function get_config(){
		$config = $this->input->post('config');
		$response->config = $this->config->item($config);
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}
	/* END Other Global Functions */

}

/* End of file */
/* Location: application/core */