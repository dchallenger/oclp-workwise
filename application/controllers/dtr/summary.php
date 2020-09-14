<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Summary extends MY_Controller
{
	private $_special = array(
				'reg_ot',
				'reg_nd',
				'reg_ndot',
				'rd_ot',
				'rd_ndot',
				'leg_ot',
				'leg_ndot',
				'spe_ot',
				'spe_ndot',
				'sperd_ot',
				'sperd_ndot',
				'legrd_ot',
				'legrd_ndot',
				'dob_ot',
				'dob_ndot',
				'dobrd_ot',
				'dobrd_ndot'
				);

	function __construct(){
		parent::__construct();
		
		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format
		
		$this->listview_title = '';
		$this->listview_description = 'This module lists all defined (s).';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a particular ';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about ';
	}

	// START - default module functions
	// default jqgrid controller method
	function index(){
		if($this->user_access[$this->module_id]['list'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}
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
		
		$data['default_query'] = true;
		$data['default_query_field'] = $this->db->dbprefix.$this->module_table.'.period_id';
		$data['default_query_val'] = $this->input->post('period_id');

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

	function listview() {
		if ($this->input->post('period_id')) {
			$this->filter = array('period_id', $this->input->post('period_id'));
		}

		parent::listview();
	}

	function _append_to_select()
	{
		$this->listview_qry .= ',' . implode(',', $this->_special);
	}

	function detail(){
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

	function edit(){
		parent::edit();
	
		//additional module edit routine here
		$data['show_wizard_control'] = false;
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/jquery/jquery.maskedinput-1.3.min.js"></script>';
		if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
			$data['show_wizard_control'] = true;
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
		}
		$data['content'] = 'editview';
	
		//other views to load
		$data['views'] = array();
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

	function ajax_save(){
		parent::ajax_save();

		//additional module save routine here
	}

	function delete(){
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
                
		if ($this->user_access[$this->module_id]['view']) {                
			foreach ($this->_special as $key) {
				if ($record[$key] > 0) {
		            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
		            break;
		        }
			}
		}

        $actions .= '</span>';

		return $actions;
	}

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" ) 
	{
		return '';
	}

	function get_summary_details()
	{
		$modifiers = array(
				'reg_ot' => 'Regular Overtime',
				//'reg_ot_excess' => 'Regular Overtime > 8',
				//'reg_ndot_excess' => 'Regular Night Differential Overtime > 8',
				//'reg_nd' => 'Regular Night Differential',
				'reg_ndot' => 'Regular Night Differential Overtime',				
				'rd_ot' => 'Rest Day Overtime',
				'rd_ot_excess' => 'Rest Day Overtime > 8',
				//'rd_nd' => 'Rest Day Night Differential',
				'rd_ndot' => 'Rest Day Night Differential Overtime',
				'rd_ndot_excess' => 'Rest Day Night Differential Overtime > 8',				
				'leg_ot' => 'Legal Holiday Overtime',
				'leg_ot_excess' => 'Legal Holiday Overtime > 8',
				//'leg_nd' => 'Legal Holiday Night Differential',
				'leg_ndot' => 'Legal Holiday Night Differential Overtime',				
				'leg_ndot_excess' => 'Legal Holiday Night Differential Overtime > 8',				
				'spe_ot' => 'Special Holiday Overtime',
				'spe_ot_excess' => 'Special Holiday Overtime > 8',
				//'spe_nd' => 'Special Holiday Night Differential',
				'spe_ndot' => 'Special Holiday Night Differential Overtime',
				'spe_ndot_excess' => 'Special Holiday Night Differential Overtime > 8',
				'sperd_ot' => 'Special Holiday Rest Day Overtime',
				'sperd_ot_excess' => 'Special Holiday Rest Day Overtime > 8',
				//'sperd_nd' => 'Special Holiday Rest Day Night Differential',
				'sperd_ndot' => 'Special Holiday Rest Day Night Differential Overtime',
				'sperd_ndot_excess' => 'Special Holiday Rest Day Night Differential Overtime > 8',
				'legrd_ot' => 'Legal Holiday Rest Day Overtime',
				'legrd_ot_excess' => 'Legal Holiday Rest Day Overtime > 8',
				//'legrd_nd' => 'Legal Holiday Rest Day Night Differential',
				'legrd_ndot' => 'Legal Holiday Rest Day Night Differential Overtime',
				'legrd_ndot_excess' => 'Legal Holiday Rest Day Night Differential Overtime > 8',				
				'dob_ot' => 'Double Holiday Overtime',
				'dob_ot_excess' => 'Double Holiday Overtime > 8',
				//'dob_nd' => 'Double Holiday Night Differential',
				'dob_ndot' => 'Double Holiday Night Differential Overtime',
				'dob_ndot_excess' => 'Double Holiday Night Differential Overtime > 8',
				'dobrd_ot' => 'Double Holiday Rest Day Overtime',
				'dobrd_ot_excess' => 'Double Holiday Rest Day Overtime > 8',
				//'dobrd_nd' => 'Double Holiday Rest Day Night Differential',
				'dobrd_ndot' => 'Double Holiday Rest Day Night Differential Overtime',
				'dobrd_ndot_excess' => 'Double Holiday Rest Day Night Differential Overtime > 8'
				);

		$record_id = $this->input->post('record_id');		

		$this->db->select(array_keys($modifiers));
		$this->db->where($this->key_field, $record_id);
		$this->db->where('deleted', 0);

		$record = $this->db->get($this->module_table);

		if ($record->num_rows() == 0) {
			$response->success = 0;
		} else {
			$rates = $this->db->get('day_type_and_rates');
			$rates = $rates->result_array();
			$a_rates = array();
			foreach ($rates as $r) {				
				$a_rates[$r['day_prefix'] . '_ot_code'] = $r['ot_code'];
				$a_rates[$r['day_prefix'] . '_nd_code'] = $r['nd_code'];
				$a_rates[$r['day_prefix'] . '_ndot_code'] = $r['ndot_code'];
				$a_rates[$r['day_prefix'] . '_ndot_excess_code'] = $r['ndot_excess_code'];
				$a_rates[$r['day_prefix'] . '_ot_excess_code'] = $r['ot_excess_code'];
				$a_rates[$r['day_prefix'] . '_nd_excess_code'] = $r['nd_excess_code'];				
			}			

			$response->success = 1;
			$html = '<div style="width: 400px;"><table width="100%" class="default-table">
				<thead>
					<tr>
						<th>Type</th>
						<th>Code</th>
						<th>Hours</th>
					</tr>
				</thead>
				<tbody>
			';	

			foreach ($record->row() as $label => $value) {
				if (!in_array($label, array('rd', 'leg', 'spe'))) {					

					if ($value > 0) {
						$html .= '<tr><td>' . $modifiers[$label] . '</td><td>'. $a_rates[$label . '_code'] .'</td><td>' . number_format($value, 2) . '</td></tr>';
					}
				}
			}

			$html .= '</tbody></table></div>';

			$response->html = $html;			
		}

		$this->load->view('template/ajax', array('json' => $response));
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */