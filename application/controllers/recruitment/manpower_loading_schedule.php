<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Manpower_loading_schedule extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Manpower Loading Schedule';
		$this->listview_description = 'List of Manpower Loading Schedule.';
		$this->jqgrid_title = "Manpower Loading Schedule List";
		$this->detailview_title = 'Manpower Loading Schedule Info';
		$this->detailview_description = 'This page shows detailed information about a particular manpower loading schedule.';
		$this->editview_title = 'Annual Manpower Planning Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about manpower loading schedule..';

		$draft_forms = $this->db->get_where('manpower_loading_schedule_approver', array('approver' => $this->user->user_id, 'focus' => 0, 'status' => 1));
		
		if ($draft_forms && $draft_forms->num_rows() > 0) {
			$draft_ids = array();
			
			foreach ($draft_forms->result() as $key => $value) {
				$draft_ids[] = $value->mls_id;
			}

			$this->filter = $this->db->dbprefix.$this->module_table.".{$this->key_field} NOT IN (".implode(',', $draft_ids).")";

		}
	}

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js	
		$data['content'] = 'listview';
		$data['scripts'][] = chosen_script();

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

		$rec = $this->db->get_where( $this->module_table, array( $this->key_field => $this->input->post('record_id') ) )->row();
		if($rec->employee_id != $this->user->user_id){
			// $data['buttons'] = 'template/detail-no-buttons';
			$data['buttons'] = 'recruitment/manpower_loading_schedule/details_approve_button';
		}
		else if( $rec->employee_id == $this->user->user_id ){
			if ($rec->manpower_loading_schedule_status_id == 3){
				$data['buttons'] = 'template/detail-no-buttons';
			}
		}

		//additional module detail routine here details_approve_button
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'detailview';
		
		//other views to load
		$data['views'] = array('recruitment/manpower_loading_schedule/details_detailview');
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
	
	function edit()
	{
		if($this->user_access[$this->module_id]['edit'] == 1){
			parent::edit();
			
			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}	
			$data['scripts'][] = chosen_script();
			
			$data['content'] = 'editview';
			
			$data['buttons'] = 'recruitment/manpower_loading_schedule/send_request';

			//other views to load
			$data['views'] = array('recruitment/manpower_loading_schedule/manpower_loading_schedule_gui');
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

	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}

	function ajax_save()
	{	
        parent::ajax_save();

        $this->db->where('manpower_loading_schedule_id',$this->key_field_val);
        $this->db->update('manpower_loading_schedule',array("employee_id" => $this->userinfo['user_id'],"manpower_loading_schedule_status_id" => 1));

        if ($this->input->post('record_id') == -1 ){

        	$approvers = $this->system->get_approvers_and_condition( $this->userinfo['user_id'], $this->module_id );
        	foreach($approvers as $approver){
                $approver['mls_id'] = $this->key_field_val;
                $this->db->insert('manpower_loading_schedule_approver', $approver);
            }

        }
	}

	function after_ajax_save(){
		$manpower_loading_schedule_id = $this->key_field_val;

		if ($this->input->post('record_id') <> -1){
			$this->db->delete('manpower_loading_schedule_details', array('manpower_loading_schedule_id' => $manpower_loading_schedule_id));
		}	

		$remarks = $this->input->post('remarks');
		$jan = $this->input->post('jan'); 
		$feb = $this->input->post('feb'); 
		$mar = $this->input->post('mar'); 
		$apr = $this->input->post('apr'); 
		$may = $this->input->post('may'); 
		$jun = $this->input->post('jun'); 	
		$jul = $this->input->post('jul'); 	
		$aug = $this->input->post('aug'); 
		$sep = $this->input->post('sep'); 
		$oct = $this->input->post('oct'); 
		$nov = $this->input->post('nov'); 
		$dec = $this->input->post('dec'); 																					

		if ($this->input->post('position_id'))
		{
			foreach ($this->input->post('position_id') as $index => $val){
				$array_info = array();
				$array_info['manpower_loading_schedule_id'] = $manpower_loading_schedule_id;
				$array_info['position_id'] = $val;
				$array_info['remarks'] = $remarks[$index];
				$array_info['jan'] = $jan[$index];
				$array_info['feb'] = $feb[$index];
				$array_info['mar'] = $mar[$index];
				$array_info['apr'] = $apr[$index];
				$array_info['may'] = $may[$index];
				$array_info['jun'] = $jun[$index];
				$array_info['jul'] = $jul[$index];
				$array_info['aug'] = $aug[$index];
				$array_info['sep'] = $sep[$index];
				$array_info['oct'] = $oct[$index];
				$array_info['nov'] = $nov[$index];
				$array_info['dec'] = $dec[$index];
				$this->db->insert('manpower_loading_schedule_details',$array_info);			
			}
		}

		parent::after_ajax_save();		
	}

    /**
     * Send the email to approvers.
     */
    function send_email() 
    {
        $this->db->join('user','user.employee_id=manpower_loading_schedule.employee_id');
        $this->db->join('user_company','user_company.company_id=user.company_id','left');
    	$this->db->join('manpower_loading_schedule_status','manpower_loading_schedule.manpower_loading_schedule_status_id = manpower_loading_schedule_status.manpower_loading_schedule_status_id');        
        $this->db->where('manpower_loading_schedule_id', $this->input->post('record_id'));
        $result = $this->db->get('manpower_loading_schedule');

        if (IS_AJAX && !is_null($result) && $result->num_rows() > 0) {
            $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $result->row_array();
                
                $mls_approver = array();
                $where = array();
                $ids = array();
                //get approvers via employee approver / position module
                $employee_approver_list = $this->system->get_approvers_and_condition($request['employee_id'],$this->module_id);

                foreach( $employee_approver_list as $employee_approver ){
                	// $this->db->where('user_id', $employee_approver['approver'] );
                	switch($employee_approver['condition']){
                        case 1:
                            if ($employee_approver['focus'] == 1) {
                           		$ids[] = $employee_approver['approver']; 	
                            }
                            break;
                        case 2:
                        case 3:
                   	       // $this->db->or_where('user_id', $employee_approver['approver'] );
                   	       	$ids[] = $employee_approver['approver'];
                           break;
                    }   

                }
                $where_ids = 'user_id IN('.implode(',', $ids).')';

				$this->db->where($where_ids);
                $result = $this->db->get('user');

                foreach( $employee_approver_list as $employee_approver ){
               		$mls_approver['status'] = 2;
               	 	switch($employee_approver['condition']){
                        case 1:
                        	$where['mls_id'] = $this->input->post('record_id');
                            if ($employee_approver['focus'] == 1) {
                            	$where['approver'] = $employee_approver['approver'];
                            }
                            break;
                        case 2:
                        case 3:
                           $where['mls_id'] = $this->input->post('record_id');
                           break;
                    }                        	
                	
                	$this->db->update('manpower_loading_schedule_approver', $mls_approver, $where);
                	
                }
                // Load the template.  
                $this->load->model('template');

                // If queued successfully set the status to For Approval.
                if ($result && $result->num_rows() > 0) {
            		$data['manpower_loading_schedule_status_id'] = 2;
                    $data['email_sent'] = '1';
                    $data['date_sent'] = date('Y-m-d G:i:s');                    
                    $this->db->where($this->key_field, $request[$this->key_field]);
                    $this->db->update($this->module_table, $data);

			        $this->db->join('user','user.employee_id=manpower_loading_schedule.employee_id');
			        $this->db->join('user_company','user_company.company_id=user.company_id','left');
			    	$this->db->join('manpower_loading_schedule_status','manpower_loading_schedule.manpower_loading_schedule_status_id = manpower_loading_schedule_status.manpower_loading_schedule_status_id');        
			        $this->db->where('manpower_loading_schedule_id', $this->input->post('record_id'));
			        $result_mls = $this->db->get('manpower_loading_schedule');
					$request = $result_mls->row_array();

			        foreach ($result->result() as $row) {
			        	if ($row->email != ''){
		                    $request['approver_user'] = $row->salutation." ".$row->lastname;
		                    
		                    $recepients = $row->email;

		                    $request['here']=base_url().'recruitment/manpower_loading_schedule/detail/'.$request['manpower_loading_schedule_id'];

		                    $request['date_created'] = date($this->config->item('display_date_format'),strtotime($request['date_created']));

		                    $template = $this->template->get_module_template($this->module_id, 'mls_status_email');
		                	$message = $this->template->prep_message($template['body'], $request);
		                	
		                	$this->template->queue($recepients, '', $template['subject'], $message);
	                	}
	                }
                }
            }
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }	

	function send_status_email( $record_id, $status_id, $decline_remarks = false){
        $this->db->join('user','user.employee_id=manpower_loading_schedule.employee_id');
        $this->db->join('user_company','user_company.company_id=user.company_id','left');
    	$this->db->join('manpower_loading_schedule_status','manpower_loading_schedule.manpower_loading_schedule_status_id = manpower_loading_schedule_status.manpower_loading_schedule_status_id');        
        $this->db->where('manpower_loading_schedule_id', $record_id);
        $result = $this->db->get('manpower_loading_schedule');

        if (IS_AJAX && $result->num_rows() > 0) {
             $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $result->row_array();
                
                $this->db->where('user_id', $request['employee_id']);
                $result = $this->db->get('user');
                // Load the template.  
                $this->load->model('template');

                // If queued successfully set the status to For Approval.
                if ($result && $result->num_rows() > 0) {
	                switch($status_id){
	                    case 3:
							$request['status'] = "Approved";	                    
	                        $data['manpower_loading_schedule_status_id'] = 3;
	                    break;
	                    case 4:
	                        $request['status'] = "For Evaluation";
	                        $data['manpower_loading_schedule_status_id'] = 4;
	                    break;
	                    case 5:
	                        $request['status'] = "Cancelled";
	                        $data['manpower_loading_schedule_status_id'] = 5;
	                    break;
	                }

                    $data['email_sent'] = '1';
                    $data['date_sent'] = date('Y-m-d G:i:s');                    
                    $this->db->where($this->key_field, $request[$this->key_field]);
                    $this->db->update($this->module_table, $data);

			        $this->db->join('user','user.employee_id=manpower_loading_schedule.employee_id');
			        $this->db->join('user_company','user_company.company_id=user.company_id','left');
			    	$this->db->join('manpower_loading_schedule_status','manpower_loading_schedule.manpower_loading_schedule_status_id = manpower_loading_schedule_status.manpower_loading_schedule_status_id');        
			        $this->db->where('manpower_loading_schedule_id', $record_id);
			        $result_mls = $this->db->get('manpower_loading_schedule');
					$request = $result_mls->row_array();

	                // decline remarks
	                if($decline_remarks)
	                	$request['decline_remarks'] = $decline_remarks;
	                else
	                	$request['decline_remarks'] = '';

			        foreach ($result->result() as $row) {
	                    $request['approver_user'] = $row->salutation." ".$row->lastname;
	                    
	                    $recepients = $row->email;

	                    $request['here']=base_url().'recruitment/manpower_loading_schedule/detail/'.$request['manpower_loading_schedule_id'];

	                    $request['date_created'] = date('Y-m-d',strtotime($request['date_created']));

	                    $template = $this->template->get_module_template($this->module_id, 'mls_status_email');
	                	$message = $this->template->prep_message($template['body'], $request);
	                	
	                	$this->template->queue($recepients, '', $template['subject'], $message);
	                }
                }
            }
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }

	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{
        $this->db->join('user','user.employee_id=manpower_loading_schedule.employee_id','left');
    	$this->db->join('hr_employee','hr_employee.employee_id = '.$this->module_table.'.employee_id','left');
    	$this->db->join('manpower_loading_schedule_status','manpower_loading_schedule.manpower_loading_schedule_status_id = manpower_loading_schedule_status.manpower_loading_schedule_status_id');        
        $this->db->where($this->key_field, $record[$this->key_field]);
        $rec = $this->db->get( $this->module_table)->row();


        // get approvers
        $is_approver = false;
        $approver = $this->db->get_where('manpower_loading_schedule_approver', array( 'mls_id' => $rec->manpower_loading_schedule_id, 'approver' => $this->user->user_id));
        if ($approver && $approver->num_rows() > 0) {
        	$approver = $approver->row();
        	$is_approver = true;
		}
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';

		// comment this field as per discussion of sir marvin - tin
		/*if( $rec->manpower_loading_schedule_status_id == 2 && $this->user_access[$this->module_id]['approve'] == 1 && $rec->employee_id != $this->user->user_id){
			if ($is_approver) {
        		if ($approver->focus != 0 && $approver->status == 2) {
        			$actions .= '<a class="icon-button icon-16-approve approve-single" tooltip="Approve" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
        		}
        	}

        	if ($this->is_superadmin) {
        		$actions .= '<a class="icon-button icon-16-approve approve-single" tooltip="Approve" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
        	}
			
		}

		if( $rec->manpower_loading_schedule_status_id == 2 && $this->user_access[$this->module_id]['decline'] == 1 && $rec->employee_id != $this->user->user_id){
			if ($is_approver) {
        		if ($approver->focus != 0 && $approver->status == 2) {
        			$actions .= '<a class="icon-button icon-16-cancel decline-single" tooltip="Disapprove" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
        		}
        	}

        	if ($this->is_superadmin) {
        		$actions .= '<a class="icon-button icon-16-cancel decline-single" tooltip="Disapprove" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
        	}
			
			
		}*/

        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
        
		if ( $this->user_access[$this->module_id]['edit'] && $rec->manpower_loading_schedule_status_id == 1 && $rec->employee_id == $this->user->user_id ) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
				
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        if ($this->user_access[$this->module_id]['delete'] && in_array( $rec->manpower_loading_schedule_status_id, array(1,2)) && $rec->employee_id == $this->user->user_id || ($this->is_superadmin)) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}  

	function change_status($record_id = 0, $non_ajax = 0) {

		if ( $this->input->post('record_id') && $non_ajax == 0 ) {
			$record_id = $this->input->post('record_id');
		}

		$this->db->where($this->key_field, $record_id);
		$result = $this->db->get($this->module_table);
		$request = $result->row_array();
		$form_status_id = $this->input->post('form_status_id');
		$this->load->helper('date');

		$approver = $this->db->get_where('manpower_loading_schedule_approver', array('approver' => $this->user->user_id,  'mls_id' => $record_id))->row();

		$this->db->update('manpower_loading_schedule_approver', array('status' => $this->input->post('form_status_id')), array('approver' => $this->user->user_id,  'mls_id' => $record_id));

		$set = array();
		$set['status'] = $this->input->post('form_status_id');

		// Check if current user is part of approvers.
		if ( IS_AJAX ) {
			switch( $this->input->post('form_status_id') ){
				case 3:
					$returnstatus = 'approved';
					break;
				case 4: 
					$returnstatus = 'disapproved';
					break;
				case 5: 
					$returnstatus = 'cancelled';
					break;
				case 6: 
					$returnstatus = 'for HR validation';
					break;
				case 7: 
					$returnstatus = 'fit to work';
					break;			
			}

			$response['message'] = 'Request ' . $returnstatus;
			switch( $this->input->post('form_status_id') ){
				case 3:

				$set['date_approved'] = date('Y-m-d H:i:s');

					switch( $approver->condition ){
	                    case 1: //by level
	                        //get next approver
	                        $next_approver = $this->db->get_where('manpower_loading_schedule_approver', array('sequence' => ($approver->sequence+1), 'mls_id' => $record_id));

	                        if( $next_approver->num_rows() == 1 ){
	                            $next_approver = $next_approver->row();
	                            $this->db->update('manpower_loading_schedule_approver', array('focus' => 1, 'status' => 2), array('sequence' => $next_approver->sequence, 'mls_id' => $record_id));
	                           
	                            //email next approver
	                            $data['manpower_loading_schedule_status_id'] = 2;
	                            $data['date_approved'] = '';
	                            $this->send_email();
	                        }
	                        else{
	                            //this is last approver
	                            $data['date_approved'] = date('Y-m-d H:i:s', now());
	                            $data['manpower_loading_schedule_status_id'] = 3;
	                            $this->send_status_email($record_id,3);	
	                            // $this->send_email_approve($record_id);
	                        }
	                        break;
	                    case 2: // Either
	                    	$data['date_approved'] = date('Y-m-d H:i:s', now());
	                        $data['manpower_loading_schedule_status_id'] = 3;
	                    	$this->send_status_email($record_id,3);	
	                        // $this->send_email_approve($record_id);
	                        break;
	                    case 3: // All
	                        $qry = "SELECT * FROM {$this->db->dbprefix}manpower_loading_schedule_approver where mls_id = {$record_id} and status != 3";
	                        $all_approvers = $this->db->query( $qry );
	                        
	                        if( $all_approvers->num_rows() == 0 ){
	                        	$data['date_approved'] = date('Y-m-d H:i:s', now());
	                            $data['manpower_loading_schedule_status_id'] = 3;
	                            $this->send_status_email($record_id,3);	
	                       		// $this->send_email_approve($record_id);
	                        }else{
	                        	$data['manpower_loading_schedule_status_id'] = 2;
	                            $data['date_approved'] = '';
	                        }
	                        break;  
	                }
					
					$this->db->where($this->key_field, $record_id);
					$this->db->update($this->module_table, $data);	
					break;
				case 4:
					$set['date_cancelled'] = date('Y-m-d H:i:s');

					$data['decline_remarks'] = $this->input->post('decline_remarks');
					$data['date_approved'] = date('Y-m-d H:i:s', now());
					$data['manpower_loading_schedule_status_id'] = 4;
					$this->db->where($this->key_field, $record_id);
					$this->db->update($this->module_table, $data);
					$this->send_status_email($record_id, 4, $this->input->post('decline_remarks'));	
					break;
				case 5:
					$set['date_cancelled'] = date('Y-m-d H:i:s');

					$data['decline_remarks'] = $this->input->post('decline_remarks');				
					$data['date_approved'] = date('Y-m-d H:i:s', now());
					$data['manpower_loading_schedule_status_id'] = 5;
					$this->db->where($this->key_field, $record_id);
					$this->db->update($this->module_table, $data);
					$this->send_status_email($record_id,5,$this->input->post('decline_remarks'));	
					break;
				case 6:
					$data['date_approved'] = date('Y-m-d H:i:s', now());
					$data['manpower_loading_schedule_status_id'] = 6;
					$this->db->where($this->key_field, $record_id);
					$this->db->update($this->module_table, $data);
					break;
				case 7:
					$data['manpower_loading_schedule_status_id'] = 7;
					$this->db->where($this->key_field, $record_id);
					$this->db->update($this->module_table, $data);
					$this->db->update('leave_approver', array('status' => 2), array('leave_id' => $record_id));
					break;	
			}

			$this->db->update('manpower_loading_schedule_approver', $set, array('approver' => $this->user->user_id, 'mls_id' => $record_id));


			$response['record_id'] = $this->input->post('record_id'); 
			$response['type'] = 'success';
			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}
		else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}	 

	function get_division_head(){
		if( $this->user_access[$this->module_id]['post'] != 1 ){

			$this->db->join('user_company_division','user.division_id = user_company_division.division_id');
			$this->db->where('user.deleted',0);
			$this->db->where('user_id',$this->userinfo['user_id']);
			$result = $this->db->get('user')->row();

			$this->db->where('user_id',$result->division_manager_id);
			$div_head = $this->db->get('user');

			$html = '';
			if ($div_head && $div_head->num_rows() > 0){
				foreach ($div_head->result() as $row) {
					$html .= '<option value="'.$row->employee_id.'">'.$row->firstname.'&nbsp;'.$row->lastname.'</option>';
				}
			}
		}

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	} 

	function get_project_cost_code(){
		$this->db->where('deleted',0);
		$this->db->where('project_name_id', $this->input->post('project_name_id'));
		$result = $this->db->get('project_name')->row();

		$data['json'] = $result->cost_code;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);					
	}	

	function get_amp_user_type(){
		$amp_user_type = "";

		if( $this->user_access[$this->module_id]['post'] == 1 ){
			$amp_user_type = "hr";
			$response['employee_id'] = "";			
		}
		else{
			$response['category_id'] = "";
			$response['category_value_id'] = "";
			$amp_user_type = "employee";
			$response['employee_id'] = $this->userinfo['user_id'];

			// $this->db->join('employee_work_assignment_category', 'employee_work_assignment_category.employee_work_assignment_category_id = employee_work_assignment.employee_work_assignment_category_id', 'left');
			$category = $this->db->get_where('user', array('user_id'=> $this->userinfo['user_id'], 'user.deleted' => 0));
			
			if ($category && $category->num_rows() > 0) {
				$category = $category->row();
				// $response['category_id'] = $category->employee_work_assignment_category_id;

					// switch ($category->employee_work_assignment_category_id) {
					// 	case 2: //by project
							$response['category_value_id'] = $category->project_name_id;
							// break;	
					// }				    
			}
			
		}

		$response['amp_user_type'] = $amp_user_type;
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
	}

	// END custom module funtions
}

/* End of file */
/* Location: system/application */
?>