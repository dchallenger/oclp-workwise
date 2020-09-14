<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Applicant_candidates extends MY_Controller {

	function __construct() {
		parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Applicants';
		$this->listview_description = 'Lists all applicants';
		$this->jqgrid_title = "Applicants List";
		$this->detailview_title = 'Applicant Info';
		$this->detailview_description = 'This page shows detailed information about a particular applicant';
		$this->editview_title = 'Applicant Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an applicant';
	}

	// START - default module functions
	// default jqgrid controller method
	function index() {
		$data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
		$data['content'] = 'listview';

		if ($this->session->flashdata('flashdata')) {
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'] . '/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

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
		
	}

	function edit() {
		
	}

	function ajax_save() {
		
	}

	// END - default module functions
	// START custom module funtions
	function show_related_module() {
		$data = array(
		    'other' => $this->input->post('other'),
		    //'searchform' => 'recruitment/manpower/candidates/searchform'
			'additional_search_options' => array("skill_type"=>"Skill Type","skill_name"=>"Skill Name","proficiency"=>"Proficiency","course"=>"Course")
		);		
		$this->load->vars($data);
		parent::show_related_module();
	}

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
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else {			
			$search = '(' . $this->db->dbprefix . $this->module_table . '.position_id IN (SELECT position_id FROM '.$this->db->dbprefix.'recruitment_manpower WHERE request_id =' . $this->input->post('other') . ')';
			$search .= ' OR ' .$this->db->dbprefix . $this->module_table . '.af_pos_id IN (SELECT position_id FROM '.$this->db->dbprefix.'recruitment_manpower WHERE request_id =' . $this->input->post('other') . '))';
		}			
/*		if ($this->input->post('_search') == 'true')
			$search = $this->_generate_search_string();
		else {			
			$search = '(' . $this->db->dbprefix . $this->module_table . '.position_id IN (SELECT position_id FROM '.$this->db->dbprefix.'recruitment_manpower WHERE request_id =' . $this->input->post('other') . ')';
			$search .= ' OR ' .$this->db->dbprefix . $this->module_table . '.af_pos_id IN (SELECT position_id FROM '.$this->db->dbprefix.'recruitment_manpower WHERE request_id =' . $this->input->post('other') . '))';
		}*/

		if ($this->module == "user" && (!$this->is_admin && !$this->is_superadmin))
			$search .= ' AND ' . $this->db->dbprefix . 'user.user_id NOT IN (1,2)';

		$this->db->where($this->module_table . '.applicant_id NOT IN (SELECT applicant_id FROM '.$this->db->dbprefix.'recruitment_manpower_candidate WHERE deleted = 0 AND candidate_status_id <> 11)');
		$this->db->where_not_in('application_status_id', array(2,4,6));

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->where($this->module_table . '.deleted = 0');
		$this->db->where($search);

		//get list
		$result = $this->db->get();

		//dbug($this->db->last_query());die();
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

			$this->db->where($this->module_table . '.deleted = 0');
			$this->db->where($search);

			$this->db->where($this->module_table . '.applicant_id NOT IN (SELECT applicant_id FROM '.$this->db->dbprefix.'recruitment_manpower_candidate WHERE deleted = 0 AND candidate_status_id <> 11)');
			$this->db->where_not_in('application_status_id', array(2,4,6));

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

/*			dbug($this->listview_columns);
			return;*/

			//dbug($this->db->last_query());
			//check what column to add if this is a related module
			if ($related_module) {
				foreach ($this->listview_columns as $column) {
					if ($column['name'] != "action") {
						$temp = explode('.', $column['name']);
						if (strpos($this->input->post('column'), ',')) {
							$column_lists = explode(',', $this->input->post('column'));
							if (sizeof($temp) > 1 && in_array($temp[1], $column_lists))
								$column_to_add[] = $column['name'];
						}
						else {
							if (sizeof($temp) > 1 && $temp[1] == $this->input->post('column'))
								$this->related_module_add_column = $column['name'];
						}
					}
				}
				//in case specified related column not in listview columns, default to 1st column
				if (!isset($this->related_module_add_column)) {
					if (sizeof($column_to_add) > 0)
						$this->related_module_add_column = implode('~', $column_to_add);
					else
						$this->related_module_add_column = $this->listview_columns[0]['name'];
				}
			}

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
									$cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions($row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr')) : $this->_default_grid_actions($this->module_link, $this->input->post('container'), $row[$this->key_field]) );
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

	function _generate_search_string() {
		// Get module columns.
		$columns = $this->db->list_fields($this->module_table);
		$search = array();		

		parse_str($this->input->post('query'), $query);

		foreach ($query as $key => $value) {
			if (in_array($key, $columns) && trim($value) != '')
				$search[$key] = $value;
		}

		return $search;
	}

	function _default_field_module_link_actions($keyfield_id = 0, $container = '', $fmlinkctr = 0) {
		$actions = '<span class="icon-group"><a class="icon-button icon-16-add" tooltip="Add" href="javascript:void(0)" onclick="shortlist(\'' . $keyfield_id . '\', \'' . $fmlinkctr . '\')"></a></span>';
		return $actions;
	}

	function _set_left_join() {
		parent::_set_left_join();

		$this->db->join('recruitment_applicant_skill', 'recruitment_applicant_skill.applicant_id = recruitment_applicant.applicant_id', 'left');
		$this->db->join('recruitment_applicant_training', 'recruitment_applicant_training.applicant_id = recruitment_applicant.applicant_id', 'left');
	}
}

/* End of file */
/* Location: system/application */