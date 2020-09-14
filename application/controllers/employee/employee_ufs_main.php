<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_ufs_main extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists employee health information.';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about an employee health information.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an employee health information.';
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

		if($this->user_access[$this->module_id]['post']==0)
		{
			 $this->load->helper('url');
			 redirect(base_url().'employee/employee_ufs_main/edit','location');
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

		//$data['buttons'] = $this->module_link . '/detail-buttons';	

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
		if($this->user_access[$this->module_id]['post']==0)
		{
			$curdate=date('Y-m-d');
			$this->db->where('date_period_to >=', $curdate);
			$this->db->where('date_period_from <=', $curdate);
			$counted=$this->db->get('employee_ufs_main');
			$id=$counted->row_array();
				if($counted->num_rows() == 0)
				{
					$this->session->set_flashdata('flashdata', 'No Survey Today');
					redirect(base_url());
				}
				else{
					$_POST['record_id'] = '-1';
						if($this->user_access[$this->module_id]['edit'] == 1){
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
							$data['views'] = array();
							$data['views_outside_record_form'] = array();

							//$data['buttons'] = $this->module_link . '/edit-buttons';

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
		}
		else{
		//$_POST['record_id'] = '-1';
		if($this->user_access[$this->module_id]['edit'] == 1){
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
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			//$data['buttons'] = $this->module_link . '/edit-buttons';

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
			}//end big else
	}

	function ajax_save()
	{
		if($this->input->post('record_id')!='-1')
		{
			$fromflagCount=0;
			$toflagCount=0;
			$fromdate=date('Y-m-d',strtotime($this->input->post('date_period_from')));
			$todate=date('Y-m-d',strtotime($this->input->post('date_period_to')));

			// $this->db->where('date_period_to >=', $todate );
			// $this->db->where('date_period_from <=', $todate );
			// $tocheck=$this->db->get('employee_ufs_main');
			// if($tocheck->num_rows()!=0)
			// 	$flagCount=1;

			// $this->db->where('date_period_from <=', $fromdate );
			// $this->db->where('date_period_to >=', $fromdate );
			// $fromcheck=$this->db->get('employee_ufs_main');
			// if($fromcheck->num_rows()!=0)
			// 	$flagCount=1;

			$this->db->where('employee_ufs_main_id', $this->input->post('record_id') );
			$this->db->where('date_period_from =', $fromdate );
			$fromcheck=$this->db->get('employee_ufs_main');
			if($fromcheck->num_rows()==0)
			{
				$this->db->where('date_period_to >=', $fromdate );
				$this->db->where('date_period_from <=', $fromdate );
				$this->db->where('employee_ufs_main_id !=', $this->input->post('record_id') );
				$tocheck=$this->db->get('employee_ufs_main');
				if($tocheck->num_rows()!=0)
					$fromflagCount=1;
			}
			else
			{
				$fromflagCount=0;
			}

			$this->db->where('employee_ufs_main_id', $this->input->post('record_id') );
			$this->db->where('date_period_to =', $todate );
			$fromcheck=$this->db->get('employee_ufs_main');
			if($fromcheck->num_rows()==0)
			{	
				$this->db->where('date_period_to >=', $todate );
				$this->db->where('date_period_from <=', $todate );
				$this->db->where('employee_ufs_main_id !=', $this->input->post('record_id') );
				$tocheck=$this->db->get('employee_ufs_main');
				if($tocheck->num_rows()!=0)
					$toflagCount=1;
			}
			else
			{
				$toflagCount=0;
			}
			
			if($fromflagCount == 0 && $toflagCount==0)
			{
				$this->db->set('date_period_from',date('Y-m-d',strtotime($this->input->post('date_period_from'))));
				$this->db->set('date_period_to',date('Y-m-d',strtotime($this->input->post('date_period_to'))));
				$this->db->set('dummy_date_from',date('Y-m-d',strtotime($this->input->post('date_period_from'))));
				$this->db->set('dummy_date_to',date('Y-m-d',strtotime($this->input->post('date_period_to'))));
				$this->db->where('employee_ufs_main_id', $this->input->post('record_id'));
				$this->db->update('employee_ufs_main');
				parent::ajax_save();
			}
			else
			{
				$response->msg = "There is already a set survey on the date inclusive";
                $response->msg_type = "error";
                $data['json'] = $response;
                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			}
		}
		else {

		if($this->input->post('is_hr')==1)
		{
			$flagCount=0;
			$fromdate=date('Y-m-d',strtotime($this->input->post('date_period_from')));
			$todate=date('Y-m-d',strtotime($this->input->post('date_period_to')));

			$this->db->where('date_period_to >=', $todate );
			$this->db->where('date_period_from <=', $todate );
			$tocheck=$this->db->get('employee_ufs_main');
			if($tocheck->num_rows()!=0)
				$flagCount=1;

			$this->db->where('date_period_from <=', $fromdate );
			$this->db->where('date_period_to >=', $fromdate );
			$fromcheck=$this->db->get('employee_ufs_main');
			if($fromcheck->num_rows()!=0)
				$flagCount=1;

			if($flagCount == 0)
			{
				$id=$this->db->get('employee_ufs_main')->num_rows();
				$id=$id+1;
				$_POST['record_id']=$id;
				$this->db->set('date_period_from',date('Y-m-d',strtotime($this->input->post('date_period_from'))));
				$this->db->set('date_period_to',date('Y-m-d',strtotime($this->input->post('date_period_to'))));
				$this->db->set('employee_ufs_main_id',$id);
				$this->db->insert('employee_ufs_main');
				$this->db->set('dummy_date_from',date('Y-m-d',strtotime($this->input->post('date_period_from'))));
				$this->db->set('dummy_date_to',date('Y-m-d',strtotime($this->input->post('date_period_to'))));
				$this->db->set('employee_ufs_main_id',$id);
				$this->db->update('employee_ufs_main');

				//added so hr can survey
				$curdate=date('Y-m-d');
				$this->db->where('date_period_to >=', $curdate);
				$this->db->where('date_period_from <=', $curdate);
				$counted=$this->db->get('employee_ufs_main');
				$id=$counted->row_array();

				$this->db->where('employee_id', $this->input->post('employee_id'));
				$user_info=$this->db->get('user')->row_array();

					if($counted->num_rows() != 0)
					{
						for($ctr=1;$ctr<=14;$ctr++){
							$_POST['record_id']=$id['employee_ufs_main_id'];

							$this->db->set('company_id',$user_info['company_id']);
							$this->db->set('division_id',$user_info['division_id']);
							$this->db->set('segment_1_id',$user_info['segment_1_id']);
							$this->db->set('segment_2_id',$user_info['segment_2_id']);
							$this->db->set('department_id',$user_info['department_id']);

							$this->db->set('employee_ufs_main_id',$id['employee_ufs_main_id']);
							$this->db->set('employee_id',$this->input->post('employee_id'));
							$this->db->set('question_number',$ctr);
							$post_val="q".$ctr;
							$this->db->set('answer',$this->input->post($post_val));
							$comm_val="comm".$ctr;
							$this->db->set('comment',$this->input->post($comm_val));
							$this->db->insert('employee_ufs_answer');
						}
					}
					else
					{
						$response->msg = "There is already a set survey on the date inclusive";
		                $response->msg_type = "error";
		                $data['json'] = $response;
		                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
					}
				//added so hr can survey
			}
			else{
				$response->msg = "There is already a set survey on the date inclusive";
                $response->msg_type = "error";
                $data['json'] = $response;
                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	         }
		}
		else{
		$curdate=date('Y-m-d');
		$this->db->where('date_period_to >=', $curdate);
		$this->db->where('date_period_from <=', $curdate);
		$counted=$this->db->get('employee_ufs_main');
		$id=$counted->row_array();

		$this->db->where('employee_id', $this->input->post('employee_id'));
		$user_info=$this->db->get('user')->row_array();

			if($counted->num_rows() != 0)
			{
				for($ctr=1;$ctr<=14;$ctr++){

					$this->db->set('company_id',$user_info['company_id']);
					$this->db->set('division_id',$user_info['division_id']);
					$this->db->set('segment_1_id',$user_info['segment_1_id']);
					$this->db->set('segment_2_id',$user_info['segment_2_id']);
					$this->db->set('department_id',$user_info['department_id']);

					$_POST['record_id']=$id['employee_ufs_main_id'];
					$this->db->set('employee_ufs_main_id',$id['employee_ufs_main_id']);
					$this->db->set('employee_id',$this->input->post('employee_id'));
					$this->db->set('question_number',$ctr);
					$post_val="q".$ctr;
					$this->db->set('answer',$this->input->post($post_val));
					$comm_val="comm".$ctr;
					$this->db->set('comment',$this->input->post($comm_val));
					$this->db->insert('employee_ufs_answer');
				}
			}
			else
			{
				$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
				redirect(base_url());
			}
		}
		parent::ajax_save();
	}


		//parent::ajax_save();

			// $dates_affected = $this->system->get_affected_dates( '1', $this->input->post('date_period_from'), $this->input->post('date_period_to') );
			// $this->db->where('deleted','0');
	  //      	$ufs_id=$this->db->get('employee_ufs_main');
	  //       $flag=0;
	  //       if($ufs_id->num_rows()>0){
	  //           foreach($ufs_id->result_array() as $approved_ufs_id){
	  //           // $this->db->where('employee_cws_id',$approved_cws_id['employee_cws_id']);
	  //           // $date_allowed=$this->db->get('employee_cws_dates')->result_array();
	  //           	$dates_inputted=$this->system->get_affected_dates( '1', $approved_ufs_id['date_period_from'], $approved_ufs_id['date_period_to'] );;
	  //               foreach($dates_inputted as $date_checking){
	  //                   foreach($dates_affected as $date){
	  //                       $date_checking_val=date('Y-m-d', strtotime($date_checking['date']));
	  //                       $date_affected_val=date('Y-m-d', strtotime($date['date']));
	  //                       if($date_checking_val==$date_affected_val)
	  //                       {
	  //                       	echo $date_checking_val." / ".$date_affected_val;
	  //                       	$flag=1;
	  //                       }
	  //                   }
	  //               }
	  //           }
	  //       }

	  //       if($flag=1){

		//additional module save routine here

	}

	function is_done_with_survey()
	{
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$curdate=date('Y-m-d');
			$flag=1;
			if($this->input->post('is_hr')==1)
			{
				if($this->input->post('record_id') != -1)
				{
					$this->db->where('employee_ufs_main_id', $this->input->post('record_id'));
					$this->db->where('employee_id', $this->input->post('employee_id'));
					$employee=$this->db->get('employee_ufs_answer');
				}
				else
				{
					$response->msg_type = 'success';
				}

				if (!$employee || $employee->num_rows() == 0 || $flag == 0) {
					$response->msg_type = 'not_yet_allowed';
					//$response->msg 		= 'Family not found.';
				} else {
					$response->msg_type = 'success';

					$response->data = $employee->row_array();
				}
			}
			else {
				$this->db->where('date_period_to >=', $curdate);
				$this->db->where('date_period_from <=', $curdate);
				$id=$this->db->get('employee_ufs_main')->row_array();

				$this->db->where('employee_ufs_main_id', $id['employee_ufs_main_id']);
				$this->db->where('employee_id', $this->input->post('employee_id'));
				$employee = $this->db->get('employee_ufs_answer');

				if (!$employee || $employee->num_rows() == 0 || $flag == 0) {
					$response->msg_type = 'no_record';
					//$response->msg 		= 'Family not found.';
				} else {
					$response->msg_type = 'success';

					$response->data = $employee->row_array();
				}
			}
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function delete()
	{
		parent::delete();

		$this->db->where('deleted', '1');
		$id=$this->db->get('employee_ufs_main')->result_array();

		foreach($id as $toDelete)
		{
			$this->db->where('employee_ufs_main_id', $toDelete['employee_ufs_main_id']);
			$this->db->delete('employee_ufs_answer');
		}

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	function after_ajax_save()
	{
		if ($this->get_msg_type() == 'success') {
			$data['updated_by']   = $this->userinfo['user_id'];
			$data['updated_date'] = date('Y-m-d H:i:s');

			if ($this->input->post('record_id') == '-1') {
				$data['created_by']   = $this->userinfo['user_id'];
				$data['created_date'] = date('Y-m-d H:i:s');
			}

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, $data);
		}

		parent::after_ajax_save();
	}
	// END custom module funtions

	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{

		$ir = $this->db->get_where($this->module_table, array($this->key_field => $record['employee_ufs_main_id']));

		if( $ir->num_rows() == 1 ){
			$ir = $ir->row();
		}

		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
                
        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
        
		if ( $this->user_access[$this->module_id]['edit']) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
				
        if ($this->user_access[$this->module_id]['print']) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        if ($this->user_access[$this->module_id]['delete']) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}

	function print_record($record_id = 0) {
		// Get from $_POST when the URI is not present.
		if(!$this->user_access[$this->module_id]['print'] == 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);			
		}

		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template($this->module_id, 'UFS');

		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);
		if ($check_record->exist) {
			$vars = get_record_detail_array($record_id);
			$vars['date_period_from'] = date( $this->config->item('display_date_format'), strtotime( $vars['dummy_date_from'] ) );
			$vars['date_period_to'] = date( $this->config->item('display_date_format'), strtotime( $vars['dummy_date_to'] ) );
			//dbug($vars);
			$this->db->where('employee_ufs_main_id',$record_id);
			$companywide_count=$this->db->get('employee_ufs_answer')->result_array();
			for($x=1;$x<=14;$x++)
			{
				$ctr=0;
				foreach($companywide_count as $companywide_add)
				{

					if($companywide_add['question_number']==$x)
					{
						if($companywide_add['answer']=='A')
						{
							$companywide_reports[$x]['A']=$companywide_reports[$x]['A']+1;
							$ctr++;
						}
						if($companywide_add['answer']==='B')
						{
							$companywide_reports[$x]['B']=$companywide_reports[$x]['B']+1;
							$ctr++;
						}
						if($companywide_add['answer']==='C')
						{
							$companywide_reports[$x]['C']=$companywide_reports[$x]['C']+1;
							$ctr++;
						}
						if($companywide_add['answer']==='D')
						{
							$companywide_reports[$x]['D']=$companywide_reports[$x]['D']+1;
							$ctr++;
						}
						if($companywide_add['answer']==='E')
						{
							$companywide_reports[$x]['E']=$companywide_reports[$x]['E']+1;
							$ctr++;
						}
						if($companywide_add['comment']!="")
						{
							$companywide_reports[$x]['comm'].="*".$companywide_add['comment']."<br />";
						}
					}
				}
				$numofresponses[$x]=$ctr;
			}

			for($x=1;$x<=14;$x++)
			{
				if($companywide_reports[$x]['A']!="")
				{
					$vars[$x.'A'] = $companywide_reports[$x]['A']." = "; 
					$vars[$x.'A'] .= round($companywide_reports[$x]['A'] * 100 / $numofresponses[$x])."%";
				}
				else
					$vars[$x.'A']="&nbsp;";

				if($companywide_reports[$x]['B']!="")
				{
					$vars[$x.'B'] = $companywide_reports[$x]['B']." = "; 
					$vars[$x.'B'] .= round($companywide_reports[$x]['B'] * 100 / $numofresponses[$x])."%";
				}
				else
					$vars[$x.'B']="&nbsp;";

				if($companywide_reports[$x]['C']!="")
				{
					$vars[$x.'C'] = $companywide_reports[$x]['C']." = "; 
					$vars[$x.'C'] .= round($companywide_reports[$x]['C'] * 100 / $numofresponses[$x])."%";
				}
				else
					$vars[$x.'C']="&nbsp;";

				if($companywide_reports[$x]['D']!="")
				{
					$vars[$x.'D'] = $companywide_reports[$x]['D']." = "; 
					$vars[$x.'D'] .= round($companywide_reports[$x]['D'] * 100 / $numofresponses[$x])."%";
				}
				else
					$vars[$x.'D']="&nbsp;";

				if($companywide_reports[$x]['E']!="")
				{
					$vars[$x.'E'] = $companywide_reports[$x]['E']." = "; 
					$vars[$x.'E'] .= round($companywide_reports[$x]['E'] * 100 / $numofresponses[$x])."%";
				}
				else
					$vars[$x.'E']="&nbsp;";

				if($companywide_reports[$x]['comm']!="")
				{
					$vars[$x.'COM']=$companywide_reports[$x]['comm'];
				}
				else
					$vars[$x.'COM']="&nbsp;";


				$vars[$x.'NOR']=$numofresponses[$x];
			}
			$html = $this->template->prep_message($template['body'], $vars, false, true);

			// Prepare and output the PDF.
			$this->pdf->setLeftMargin('15.00');
			//$this->pdf->setPageFormat('','L');
			$this->pdf->addPage('L');
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date($this->config->item('display_datetime_format_compact')).'-UFS-'. $record_id .'.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'The Data you are trying to access does not exist.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}



	function get_prev_info() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->where('employee_ufs_main_id',$this->input->post('record_id'));
			$employee = $this->db->get('employee_ufs_main');

			

			if (!$employee || $employee->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'Employee not found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->row_array();
			}			
		}

		$this->load->view('template/ajax', array('json' => $response));
	}


	function get_report() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
		$no_val_report=0;
		$this->db->where('employee_ufs_main_id',$this->input->post('record_id'));
		$this->db->where('company_id',$this->input->post('company_id'));
		$this->db->where('department_id',$this->input->post('department_id'));
		$this->db->where('segment_1_id',$this->input->post('segment_1_id'));
		$this->db->where('segment_2_id',$this->input->post('segment_2_id'));
		$this->db->where('division_id',$this->input->post('division_id'));
		$companywide_count=$this->db->get('employee_ufs_answer');
		
		if($companywide_count->num_rows()!=0)
		{
		$companywide_count=$companywide_count->result_array();
		for($x=1;$x<=14;$x++)
		{
			$ctr=0;
			foreach($companywide_count as $companywide_add)
			{

				if($companywide_add['question_number']==$x)
				{
					if($companywide_add['answer']=='A')
					{
						$companywide_reports[$x]['A']=$companywide_reports[$x]['A']+1;
						$ctr++;
					}
					if($companywide_add['answer']==='B')
					{
						$companywide_reports[$x]['B']=$companywide_reports[$x]['B']+1;
						$ctr++;
					}
					if($companywide_add['answer']==='C')
					{
						$companywide_reports[$x]['C']=$companywide_reports[$x]['C']+1;
						$ctr++;
					}
					if($companywide_add['answer']==='D')
					{
						$companywide_reports[$x]['D']=$companywide_reports[$x]['D']+1;
						$ctr++;
					}
					if($companywide_add['answer']==='E')
					{
						$companywide_reports[$x]['E']=$companywide_reports[$x]['E']+1;
						$ctr++;
					}
					if($companywide_add['comment']!="")
					{
						$companywide_reports[$x]['comm'].="*".$companywide_add['comment']."<br />";
					}
				}
			}

			$numofresponses[$x]=$ctr;
		}
	}// end if of if($companywide_count->num_rows()!=0)
	else
		$no_val_report=1;

	if($no_val_report==0)
	{
	$html_report='<style>
	table.padded-table td { 
		padding:10px; 
	}
	</style>
	<table width="100%" class="padded-table" style="text-align:center;">
	<th style="text-align:left;">Question</th><th style="border: solid 1px">Strongly Disagree</th><th style="border: solid 1px">&nbsp;Disagree&nbsp;</th><th style="border: solid 1px">Neither Agree nor Disagree</th><th style="border: solid 1px">&nbsp;Agree&nbsp;</th><th style="border: solid 1px">Strongly Agree</th><th style="border: solid 1px; ">No. of Responses</th><th style="border: solid 1px">Comment/s if any</th>
	<tr><td style="text-align:left;font-style:italic">When I wear the euromoney pioneer T-Shirts...</td></tr>';
		$txtctr=1;
		$txtquestion[1]="I feel excited and energetic";
		$txtquestion[2]="I feel Greater Sense of pride for Pioneer";
		$txtquestion[3]="I feel comfortable and relaxed when I wear them";
		$txtquestion[4]="I feel happy and confident about myself";
		$txtquestion[5]="I feel very professional when i'm with clients";
		$txtquestion[6]="I feel more cautious about how i carry myself because I carry the name Pioneer";
		$txtquestion[7]="I feel inspired at work";
		for($x=1;$x<=14;$x++)
		{
			if($txtctr==8) 
			{
				$html_report .= '<tr><td style="text-align:left;font-style:italic">When I wear the new set of corporate attire...</td></tr>';
				$txtctr=1;
			}
				
					$html_report .= '<tr>
									<td width="35%" style="text-align:left">'. $x.'. '.$txtquestion[$txtctr]. '</td><td style="border: solid 1px;padding-left:20px;padding-right:20px;">';
											if($companywide_reports[$x]['A']!="")
											{
												$html_report .= $companywide_reports[$x]['A']." = "; 
												$html_report .= round($companywide_reports[$x]['A'] * 100 / $numofresponses[$x])."%";
											}
					$html_report .= '</td>
									<td style="border: solid 1px">';
											if($companywide_reports[$x]['B']!="")
											{
												$html_report .= $companywide_reports[$x]['B']." = "; 
												$html_report .= round($companywide_reports[$x]['B'] * 100 / $numofresponses[$x])."%";
											}
					$html_report .= '</td>
									<td style="border: solid 1px">';
											if($companywide_reports[$x]['C']!="")
											{
												$html_report .= $companywide_reports[$x]['C']." = "; 
												$html_report .= round($companywide_reports[$x]['C'] * 100 / $numofresponses[$x])."%";
											}
					$html_report .= '</td>
									<td style="border: solid 1px">';
											if($companywide_reports[$x]['D']!="")
											{
												$html_report .= $companywide_reports[$x]['D']." = "; 
												$html_report .= round($companywide_reports[$x]['D'] * 100 / $numofresponses[$x])."%";
											}
					$html_report .= '</td>
									<td style="border: solid 1px">';
											if($companywide_reports[$x]['E']!="")
											{
												$html_report .= $companywide_reports[$x]['E']." = "; 
												$html_report .= round($companywide_reports[$x]['E'] * 100 / $numofresponses[$x])."%";
											}
					$html_report .= '</td>
									<td style="border: solid 1px">';
										$html_report .= $numofresponses[$x];
					$html_report .= '</td>
									<td width="20%" style="border: solid 1px">';
										$html_report .= $companywide_reports[$x]['comm']; 
					$html_report .= '</td>
								</tr>';

					$txtctr++;
			}	
	}//end if $no_val_report flag

			 if ($no_val_report==1) {
			 	$response->msg_type = 'error';
			 	$response->msg 		= 'No Employee have answer the survey with that query';
			 } else {
				$response->msg_type = 'success';

				$response->html = $html_report;
			 }			
		}

		$this->load->view('template/ajax', array('json' => $response));
	}


}

/* End of file */
/* Location: system/application */