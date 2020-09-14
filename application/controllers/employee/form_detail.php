<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of detail
 *
 * @author jconsador
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Form_detail extends MY_Controller {

	function __construct() {
		parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->module_table = 'module';
		$this->key_field = 'module_id';	
		
		$this->listview_title = 'Manage 201 Form Details';
		$this->listview_description = '';
		$this->jqgrid_title = '';
		$this->detailview_title = '';
		$this->detailview_description = '';
		$this->editview_title = 'Add/Edit';
		$this->editview_description = '';
	}

	// START - default module functions
	// default jqgrid controller method
	function index() {
		$data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
		$data['content'] = 'listview';

		$this->db->where('code', '201_details');
		$result = $this->db->get('module');
		$parent = $result->row();
		
		$this->parent_id = $parent->module_id;
		
		if ($this->session->flashdata('flashdata')) {
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'] . '/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons('admin/module');

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footer
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function detail() {
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';

		$data['content'] = 'detailview';

		//other views to load
		$data['views'] = array();

		if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
			$data['show_wizard_control'] = true;
		}

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footer
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function edit() {
		if ($this->user_access[$this->module_id]['edit'] == 1) {
			$this->load->helper('form');

			parent::edit();

			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';

			if (!empty($this->module_wizard_form) && $this->input->post('record_id') == '-1') {
				$data['show_wizard_control'] = true;
				$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form.js"></script>';
			}
			$data['content'] = 'editview';

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			//load variables to env
			$this->load->vars($data);

			//load the final view
			//load header
			$this->load->view($this->userinfo['rtheme'] . '/template/header');
			$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

			//load page content
			$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

			//load footer
			$this->load->view($this->userinfo['rtheme'] . '/template/footer');
		} else {
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function ajax_save() {
		parent::ajax_save();
	}

	function delete() {
		parent::delete();
	}

	// END - default module functions
	// START custom module funtions
	function listview() {
		$response->msg = "";

		$page = $this->input->post('page');
		$limit = $this->input->post('rows'); // get how many rows we want to have into the grid
		$sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
		$sord = $this->input->post('sord'); // get the direction
		$related_module = ( $this->input->post('related_module') ? true : false );

		$view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true;

		//set columnlist and select qry
		$this->_set_listview_query('', $view_actions);

		//set Search Qry string
		if ($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;

		if ($this->module == "user" && (!$this->is_admin && !$this->is_superadmin))
			$search .= ' AND ' . $this->db->dbprefix . 'user.user_id NOT IN (1,2)';

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->where($this->module_table . '.deleted = 0 AND ' . $search);
		$this->db->where('parent_id', '(SELECT module_id FROM ' . $this->db->dbprefix . $this->module_table . ' WHERE code = "201_details")', false);

		//get list
		$result = $this->db->get();
		//dbug($this->db->last_query());
		if ($this->db->_error_message() != "") {
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		} else {
			$total_pages = $result->num_rows() > 0 ? ceil($result->num_rows() / $limit) : 0;
			$response->page = $page > $total_pages ? $total_pages : $page;
			$response->total = $total_pages;
			$response->records = $result->num_rows();

			/* record query */
			//build query
			$this->_set_left_join();
			$this->db->select($this->listview_qry, false);
			$this->db->from($this->module_table);

			$this->db->where($this->module_table . '.deleted = 0 AND ' . $search);
			$this->db->where('parent_id', '(SELECT module_id FROM ' . $this->db->dbprefix . $this->module_table . ' WHERE code = "201_details")', false);

			if ($sidx != "") {
				$this->db->order_by($sidx, $sord);
			} else {
				if (is_array($this->default_sort_col)) {
					$sort = implode(', ', $this->default_sort_col);
					$this->db->order_by($sort);
				}
			}
			$start = $limit * $page - $limit;
			$this->db->limit($limit, $start);

			$result = $this->db->get();

			if ($this->db->_error_message() != "") {
				$response->msg = $this->db->_error_message();
				$response->msg_type = "error";
			} else {
				$response->rows = array();
				if ($result->num_rows() > 0) {
					$columns_data = $result->field_data();
					$column_type = array();
					foreach ($columns_data as $column_data) {
						$column_type[$column_data->name] = $column_data->type;
					}
					$this->load->model('uitype_listview');
					$ctr = 0;
					foreach ($result->result_array() as $row) {
						$cell = array();
						$cell_ctr = 0;
						foreach ($this->listview_columns as $column => $detail) {
							if (preg_match('/\./', $detail['name'])) {
								$temp = explode('.', $detail['name']);
								$detail['name'] = $temp[1];
							}

							if ($detail['name'] == 'action') {
								if ($view_actions) {
									$cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions($row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr')) : $this->_default_grid_actions('admin/module', $this->input->post('container'), $row[$this->key_field]) );
									$cell_ctr++;
								}
							} else {
								if (in_array($this->listview_fields[$cell_ctr]['uitype_id'], array(2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33))) {
									$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
									$cell[$cell_ctr] = $this->uitype_listview->fieldValue($this->listview_fields[$cell_ctr]);
								} else if (in_array($this->listview_fields[$cell_ctr]['uitype_id'], array(3)) && ( isset($this->listview_fields[$cell_ctr]['other_info']['picklist_type']) && $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] == 'Query' )) {
									$cell[$cell_ctr] = "";
									foreach ($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val) {
										if ($row[$detail['name']] == $picklist_val['id'])
											$cell[$cell_ctr] = $picklist_val['value'];
									}
								}
								else {
									$cell[$cell_ctr] = (is_numeric($row[$detail['name']]) && ($column_type[$detail['name']] != "253" && $column_type[$detail['name']] != "varchar") ) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
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

		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	function _default_grid_buttons($module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "") {
		// set default
		if ($module_link == "")
			$module_link = $this->module_link;
		if ($addtext == "")
			$addtext = "Add";
		if ($deltext == "")
			$deltext = "Delete";
		if ($container == "")
			$container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";

		if ($this->user_access[$this->module_id]['add']) {
			$buttons .= "<div class='icon-label'>";
			$buttons .= "<a parent='" . $this->parent_id . "' class='icon-16-add icon-16-add-listview' related_field='" . $related_field . "' related_field_value='" . $related_field_value . "' module_link='" . $module_link . "' href='javascript:void(0)'>";
			$buttons .= "<span>" . $addtext . "</span></a></div>";
		}

		if ($this->user_access[$this->module_id]['delete']) {
			$buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='" . $container . "' module_link='" . $module_link . "' href='javascript:void(0)'><span>" . $deltext . "</span></a></div>";
		}

		$buttons .= "</div>";

		return $buttons;
	}
}

/* End of file */
/* Location: system/application */
