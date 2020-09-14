<?php

include (APPPATH . 'controllers/recruitment/manpower.php');

class Firstbalfour_manpower extends Manpower
{
	public function __construct() {
		parent::__construct();
	}	

	/**
	 * Returns a json encoded array of company positions
	 */
	function get_company_positions() {
		if (IS_AJAX) {
			$response['positions'] = '';
			$company_id = $this->input->post('company_id');

			if ($company_id > '' && $this->input->post('category_id') == 1) {
				$this->db->where('company_id', $company_id);
				$this->db->order_by('position');
				$this->db->where('deleted', 0);
				$result = $this->db->get(self::POSITIONS_TABLE);

				$response = $this->_get_default('position_id');

				if ($result->num_rows() > 0) {
					$response['positions'] = $result->result_array();
				}
				else{
					$response['positions'] = '';
				}
			} elseif ($this->input->post('category_id') > 1) {
				$this->db->order_by('position');
				$this->db->where('deleted', 0);
				$result = $this->db->get(self::POSITIONS_TABLE);

				$response = $this->_get_default('position_id');

				if ($result->num_rows() > 0) {
					$response['positions'] = $result->result_array();
				}
				else{
					$response['positions'] = '';
				}				
			}

			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			return;
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	private function _get_default($type) {
		$record = $this->_get_mrf();
		$response['value'] = 0;
		if ($record) {
			$response['value'] = $record[$type];
		}

		return $response;
	}

	private function _get_mrf($record_id = 0) {
		if ($this->input->post('record_id') > 0 && $record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$this->db->where('request_id', $record_id);
		$result = $this->db->get(self::MANPOWER_REQUEST_TABLE);

		if ($result->num_rows() > 0) {
			return $result->row_array();
		}

		return false;
	}	
}
?>