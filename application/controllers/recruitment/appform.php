<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Appform extends MY_Controller {

	private $_detail_types;

	function __construct() {
		// Call CI constructor so we can access CI's global class.
		CI_Controller::__construct();

		$this->load->add_package_path(MODPATH . CLIENT_DIR);
		$this->load->config('client');
		
		$this->module = $controller = $this->router->fetch_class();
		$this->method = $data['method'] = $this->router->fetch_method();

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
		//set module name and id
		$this->_set_module_detail( $this->module_link );

		// Get default themes.
		$this->userinfo['rtheme'] = $this->config->item('default_theme');
		$this->userinfo['theme']  = 'themes/' . $this->userinfo['rtheme'];

		$this->user_access[$this->module_id]['add']  = 1;
		$this->user_access[$this->module_id]['edit'] = 1;
		$this->user_access[$this->module_id]['view'] = 1;
		$this->user_access[$this->module_id]['list'] = 1;		

		$this->load->helper('form');

		$this->_detail_types = array('education', 'employment', 'family', 'references','referral', 'training', 'affiliates', 'skill','test_profile');
		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->editview_title         = 'Applicant Add/Edit';
		$this->editview_description   = 'This page allows saving/editing information about an applicant';

		if (method_exists($this, 'print_record')) {
			$data['show_print'] = true;
		} else {
			$data['show_print'] = false;
		}

		$this->load->vars($data);
	}

	// START - default module functions
	// default jqgrid controller method
	function index() {
		$this->edit();
	}

	function detail() {
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/recruitment/applicants_detailview.js"></script>';

		if (IS_AJAX && $this->input->post('flag') == 0) {
			$data['content'] = 'recruitment/detailview';
		} else {
			$data['content'] = 'recruitment/compactview';
		}

		//other views to load
		$data['views'] = array();

		$record_id = $this->input->post('record_id');


		foreach ($this->_detail_types as $detail) {
			$data[$detail] = $this->_get_applicant_detail($detail);
		}

		$data['applicant_name'] = 'New Applicant';

		if ($record_id > 0) {
			$this->db->limit(1);
			$applicant = $this->db->get_where('recruitment_applicant', array('applicant_id' => $record_id))->row();

			$data['applicant_name'] = $applicant->lastname . ', ' . $applicant->firstname;

			if ($applicant->photo != '' && file_exists($applicant->photo)) {
				$data['photo'] = $applicant->photo;
			}
		}

		$data['wizard_header'] = 'recruitment/applicants/wizard_header';

		if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
			$data['show_wizard_control'] = true;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';
		}
		//load variables to env
		$this->load->vars($data);

		if (!IS_AJAX) {
			//load the final view
			//load header
			$this->load->view($this->userinfo['rtheme'] . '/template/header');
			$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

			//load page content
			$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

			//load footer
			$this->load->view($this->userinfo['rtheme'] . '/template/footer');
		} else {
			$data['html'] = $this->load->view($this->userinfo['rtheme'] . '/' . $data['content'], '', true);

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}
	}

	function edit() {

		if ($this->user_access[$this->module_id]['edit'] == 1) {			
			if (!$this->input->post('record_id')) {				
				$_POST['record_id'] = '-1';
			}
			
			parent::edit();

			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/recruitment/applicants.js"></script>';
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/recruitment/applicant_common.js"></script>';
			$data['scripts'][] = '<link type="text/css" href="' . base_url() . 'appform/css/style.css" rel="stylesheet"></link>';
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/jquery/jquery.maskedinput-1.3.min.js"></script>';
			if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
				$data['show_wizard_control'] = true;
				$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';
			}
			
			// Get default fieldgroup to open, if any.
			$default_fg = $this->input->post('default_fg');
			if (isset($default_fg) && $default_fg > 0) {
				$data['default_fg'] = $default_fg;
			}
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			$record_id = $this->input->post('record_id');

			$data['applicant_name'] = 'New Applicant';

			if ($record_id > 0) {
				$this->db->limit(1);
				$applicant = $this->db->get_where('recruitment_applicant', array('applicant_id' => $record_id))->row();

				$data['applicant_name'] = $applicant->lastname . ', ' . $applicant->firstname;
				if ($applicant->photo != '' && file_exists($applicant->photo)) {
					$data['photo'] = $applicant->photo;
				}
			}

			foreach ($this->_detail_types as $detail) {
				$data[$detail] = $this->_get_applicant_detail($detail);
			}

			//load variables to env
			$this->load->vars($data);

			//load the final view
			//load header
			$this->load->view($this->userinfo['rtheme'] . '/template/header');
			
			//load page content
			$this->load->view($this->userinfo['rtheme'] . '/recruitment/appform/editview');

			//load footer
			$this->load->view($this->userinfo['rtheme'] . '/template/footer');
		} else {
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);			
		}
	}

	function ajax_save() {


		if ($this->input->post('record_id') == '-1') {
			$_POST['application_status_id'] = 1;
			$_POST['application_date']		= date('Y-m-d H:i:s');
			$_POST['middleinitial']		= substr($this->input->post('middlename'), 0, 1);
			$_POST['aux']		= $this->input->post('aux');
		}		

		
		parent::ajax_save();

		//additional module save routine here
		if (isset($this->key_field_val) && $this->key_field_val > 0) {
			$applicant_id = $this->key_field_val;

			// Save applicant code.
			$this->db->where($this->key_field, $this->key_field_val);
			$applicant = $this->db->get($this->module_table)->row();

			if ($applicant->application_status_id != 5) {
				$status = 1;
			} else {
				$status = 5;
			}

			$uin = date('Ymd', strtotime($applicant->application_date)) . '-' . number_pad($applicant_id, '6');
			$this->db->update(
				$this->module_table, 
				array('uin' => $uin, 'application_status_id' => $status),
				array($this->key_field => $this->key_field_val)
				);
			
			// START.			
			// Process other details.

			$employment_data = array(
			    'no_work_experience' => $this->input->post('no_work_experience'),
			    'working_since' => $this->input->post('working_since')
			);

			$this->db->update('recruitment_applicant', $employment_data, array( $this->key_field => $applicant_id ));


			$data = array(
				'applicant_id' => $this->key_field_val,
				'position_applied' => $this->input->post('position_id'),
				'applied_date' => date('Y-m-d H:i:s'),
				'status' => $status,
				'mrf_id' => 0
			);

			//save aaplication
			$this->db->insert('recruitment_applicant_application',$data);

			foreach ($this->_detail_types as $detail) {


				// if( $detail == 'employment' && $this->input->post('no_work_experience') != 1 ){


				// }
				// else{


					$table = 'recruitment_applicant_' . $detail;

					if ($this->db->table_exists($table)) {
						$this->db->delete($table, array('applicant_id' => $applicant_id));
						$post = $this->input->post($detail);

						if (!is_null($post) && is_array($post)) {
							// Handle the dates.
							foreach ($post as $key => $value) {
								$key_string_segments = explode('_', $key);

								if (($detail == 'education'
									&& in_array(end($key_string_segments), array('from', 'to'))
									)
									|| end($key_string_segments) == 'date') {
									foreach ($post[$key] as &$date)
										if ($date != '') {
											$date = date('Y-m-d', strtotime($date));
										} else {
											$date = NULL;
										}
								}

								if ($detail == 'affiliates'){		

									if ((in_array(end($key_string_segments), array('from', 'to'))) || end($key_string_segments) == 'date'){
										foreach ($post[$key] as &$date){

											if($date != "" && $date != "1970-01-01" && $date != "0000-00-00" && $date !== "January 1970"){
												// $date = $date . '-01-01';
												$date = $date;
											}else{
												$date = 'NULL';
											}	
										}
									}

								}

								if ( $detail == 'test_profile' && $key == 'date_taken' ) {
									foreach ($post[$key] as &$date){
										if($date != ""){
											$date = date('Y-m-d', strtotime($date));
										}else{
											$date = $date;
										}
									}
								}

								if ( $detail == 'accountabilities' && ( $key == 'date_issued' || $key == 'date_returned') ) {
									foreach ($post[$key] as &$date){
										if($date != ""){
											$date = date('Y-m-d', strtotime($date));
										}else{
											$date = $date;
										}
									}
								}


							}

							$data = $this->_rebuild_array($post, $applicant_id);

							$this->db->insert_batch($table, $data);
						}
					}
					

				// }

			}
			//send email
			$this->send_email($applicant_id);
		 }

		
		// END.
	}

	// END - default module functions
	// START custom module funtions

	protected function after_ajax_save() {
		$image_config = array();
		// Resize image if a new one is submitted.		
		if (file_exists($this->input->post('photo'))) {
			$orig_path    = explode('/', $this->input->post('photo'));
			$orig_path[0] .= '/thumbs';
			$thumb_path   = implode('/', $orig_path);

			unset($orig_path[count($orig_path) - 1]);
			
			$thumb_dir = implode('/', $orig_path);

			$this->load->library('image_lib');
			
			$image_config['source_image']   = $this->input->post('photo');
			$image_config['create_thumb']   = TRUE;
			$image_config['maintain_ratio'] = TRUE;
			$image_config['thumb_marker']   = '';
			$image_config['new_image']      = $thumb_path;
			$image_config['width']          = 50;
			$image_config['height']         = 50;
		}

		if (count($image_config) > 0) {
			if (!is_dir($thumb_dir)) {
				if (!mkdir($thumb_dir, 0755, true)) {
				$response->msg 		= 'Could not create directory. DIR:' . $thumb_dir;
				$response->msg_type = 'attention';
				}
			}

			$this->image_lib->initialize($image_config);

			if (!$this->image_lib->resize()) {
				// How to handle error?
				$response->msg 		= $this->image_lib->display_errors();
				$response->msg_type = 'attention';

				$this->set_message($response);					
			}
		}
		
		// Reset record_id to "-1" so the form is cleared.		
		$response = parent::get_message();
		//$response->record_id = '-1';
		$response->page_refresh = "true";

		parent::set_message($response);
		parent::after_ajax_save();			
	}

	function get_form($type) {
		if (IS_AJAX) {
			if ($type == '' && !in_array($type, $this->_detail_types)) {
				show_error("Insufficient data supplied.");
			} else {

				$data['count'] = $this->input->post('counter_line');
				$data['rand'] = rand(1000,9999);

				$response = $this->load->view($this->userinfo['rtheme'] . '/recruitment/applicants/' . $type . '/form',$data);

				$data['html'] = $response;

				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			}
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	private function _get_applicant_detail($detail, $record_id = 0) {
		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$response = array();

		if ($detail == '' && !in_array($detail, $this->_detail_types)) {
			show_error("Insufficient data supplied.");
		} else {
			$table = 'recruitment_applicant_' . $detail;
			$this->db->where('applicant_id', $record_id);

			if ($detail == 'education') {
				$this->db->select($table . '.degree,'
					. $table . '.date_from,'
					. $table . '.degree,'
					. $table . '.date_to,'
					. $table . '.date_graduated,'
					. $table . '.school,'
					. $table . '.honors_received,'
					. ', do.option_id, do.value as education_level');
				$this->db->join('dropdown_options do', 'do.option_id = ' . $table . '.education_level', 'left');
			}

			$result = $this->db->get($table);

			if ($result->num_rows() == 0) {
				$response[] = $this->db->list_fields($table);
			} else {
				$response = $result->result_array();
			}				
		}

		return $response;
	}

	/**
	 * Rearrange the array to a new array which can be used for insert_batch
	 *
	 * @param array $array
	 * @param int $key
	 *
	 * @return array
	 */
	private function _rebuild_array($array, $fkey = null) {
		if (!is_array($array)) {
			return array();
		}

		$new_array = array();

		$count = count(end($array));
		$index = 0;

		while ($count > $index) {
			foreach ($array as $key => $value) {
				$new_array[$index][$key] = $array[$key][$index];
				if (!is_null($fkey)) {
					$new_array[$index]['applicant_id'] = $fkey;
				}
			}

			$index++;
		}

		return $new_array;
	}

	function delete() {
		parent::delete();

		//additional module delete routine here
		// Delete other details
		foreach ($this->_detail_types as $detail) {
			$table = 'recruitment_applicant_' . $detail;

			$this->db->where('applicant_id', $this->input->post('record_id'));
			$this->db->delete($table);
		}
	}

	function print_record($applicant_id = 0) {
		$this->load->helper('recruitment');		
		
		if (!$this->user_access[$this->module_id]['visible']) {
			$this->session->set_flashdata('flashdata', 'You dont have sufficient access to the requested module. <span class="red">Please contact the System Administrator.</span>.');
			redirect(base_url() . $this->module_link);
		}

		// Get from $_POST when the URI is not present.
		if ($applicant_id == 0) {
			$applicant_id = $this->input->post('record_id');
		}				

		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template($this->module_id, 'applicant_personal_info');		
		
		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($applicant_id);
		if ($check_record->exist) {
			$vars = array();

			// Get the vars to pass to the template.
			$vars = get_record_detail_array($applicant_id);

			//load variables to applicant view files.
			foreach ($this->_detail_types as $detail) {
				$vars[$detail] = $this->_get_applicant_detail($detail, $applicant_id);
			}									
			
			if (isset($vars['photo']) && ($vars['photo'] == '' || !file_exists($vars['photo']))) {
				$vars['photo'] = 'themes/blue/images/no-photo.jpg';
			}			

			$referred_by_choices = $this->db->get('referred_by')->result();
			
			foreach ($referred_by_choices as $choice) {
				if ($vars['referred_by_id'] == $choice->referred_by) {
					$vars[$choice->referred_by] = 'x';
				} else {
					$vars[$choice->referred_by] = '&nbsp;';
				}
			}
			
			preg_match('/([a-zA-Z]{3}\s{1}\d{2},\s\d{4})\s([0-9]{2}:[0-9]{2}\s[ap]m)/', $vars['application_date'], $x);

			$vars['application_date'] = $x[1];
			$vars['time'] 			  = $x[2];
			// Compute age.
			$vars['age'] = get_age($vars['birth_date']);

			// Suppress errors because the template model does not take into account the possibility that a variable may not have been set.
			@$html = $this->template->prep_message($template['body'], $vars, false, true);
			
			// Prepare and output the PDF.
			$this->pdf->addPage();

			$this->pdf->writeHTML($html, true, false, false, false, '');
			$this->pdf->Output(date('Y-m-d').' '.$vars['firstname'] . '_' . $vars['lastname'] . '.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function _default_grid_actions($module_link = "", $container = "") {
		// set default
		if ($module_link == "")
			$module_link = $this->module_link;
		if ($container == "")
			$container = "jqgridcontainer";

		$actions = '<span class="icon-group">';

		if ($this->user_access[$this->module_id]['view']) {
			$actions .= '<a class="icon-button icon-16-info" module_link="' . $module_link . '" tooltip="View" href="javascript:void(0)"></a>';
		}

		if ($this->user_access[$this->module_id]['print']) {
			$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
		}

		if ($this->user_access[$this->module_id]['edit']) {
			$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
		}

		if ($this->user_access[$this->module_id]['delete']) {
			$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
		}

		$actions .= '</span>';

		return $actions;
	}

	function show_related_module() {

		if (IS_AJAX) {
			$data['container'] = $this->module . '-fmlink-container';
			$data['pager']     = $this->module . '-fmlink-pager';
			$data['fmlinkctr'] = $this->input->post('fmlinkctr');

			//set default columnlist
			$this->_set_listview_query();

			//set grid buttons
			$data['jqg_buttons'] = $this->_listview_in_boxy_grid_buttons();
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/listview_in_boxy.js"></script>';

			//set load jqgrid loadComplete callback
			$data['jqgrid_loadComplete'] = "";

			$this->load->vars($data);
			$boxy = $this->load->view($this->userinfo['rtheme'] . "/listview_in_boxy", $data, true);

			$data['html'] = $boxy;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function send_email($applicant_id = 0)
	{	
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url());
		} else {
			$this->db->where('applicant_id', $applicant_id);
			$this->db->where('deleted', 0);
			$record = $this->db->get('recruitment_applicant');

			if (!$record || $record->num_rows() == 0) {
				$response->msg 	    = 'Record not found. Request was not sent.';
				$response->msg_type = 'error';
			} else {

				// Send email.
                // Load the template.            
                $this->load->model('template');

                $applicant_info = $record->row_array();

                $request['applicant_name'] = $applicant_info['firstname']." ".$applicant_info['lastname'];
                $request['position_applied_for'] = $this->db->get_where('user_position', array("position_id" => $applicant_info['position_id']))->row()->position; 

                $template = $this->template->get_module_template($this->module_id, 'application_submission');
                $message = $this->template->prep_message($template['body'], $request);

				$recepients[] = $applicant_info['email'];

                $this->template->queue(implode(',', $recepients), '', $template['subject']." : ".$applicant_info['firstname']." ".$applicant_info['lastname'], $message);

    //             $response->msg 	    = 'Your Application is successfully save.';
				// $response->msg_type = 'success';

			}

			// $data['json'] = $response;
			// $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}	
	}	

	function get_applicant_data($record_id = 0) {
		if (IS_AJAX) {
			if ($record_id == 0) {
				$record_id = $this->input->post('record_id');
			}

			if ($record_id == '') {
				$data['json'] = 0;
				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			}

			$response['type']    = 'error';
			$response['message'] = 'Record does not exist.';

			$record = get_record_detail_array($record_id, true);
			if ($record) {
				$response['type'] = 'success';
				$response['data'] = $record;

				$this->db->where('position_id', $record['position_id']);
				$this->db->select('user_position.company_id');

				$result = $this->db->get('user_position')->row_array();

				$response['data']['company_id'] = $result['company_id'];
			}

			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function check_prev_applicant()
	{
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);			
		} else {
			$this->db->where('firstname', $this->input->post('firstname'));
			$this->db->where('middlename', $this->input->post('middlename'));
			$this->db->where('lastname', $this->input->post('lastname'));
			$this->db->where('sex', $this->input->post('sex'));
			$this->db->where('birth_date', date('Y-m-d', strtotime($this->input->post('birth_date'))));
			$this->db->where('deleted', 0);			

			$result = $this->db->get($this->module_table);

			//$response->last_query = $this->db->last_query();

			if ($response->exists = $result->num_rows() > 0) {
				$response->blacklisted = ($result->row()->application_status_id == 6);
			}

			$data['json'] = $response;
			$this->load->view('template/ajax', $data);	
		}
	}

	function verify_applicant_code() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);			
		} else {
			$response->record_id = 0;

			$this->db->where('uin', $this->input->post('uin'));
			$this->db->where('firstname', $this->input->post('firstname'));
			$this->db->where('middlename', $this->input->post('middlename'));
			$this->db->where('lastname', $this->input->post('lastname'));
			$this->db->where('sex', $this->input->post('sex'));
			$this->db->where('birth_date', date('Y-m-d', strtotime($this->input->post('birth_date'))));
			$this->db->where('deleted', 0);
			
			$result = $this->db->get($this->module_table);

			if ($result->num_rows() > 0) {
				$response->record_id = $result->row()->applicant_id;
			}

			$data['json'] = $response;
			$this->load->view('template/ajax', $data);
		}		
	}
}

/* End of file */
/* Location: system/application */