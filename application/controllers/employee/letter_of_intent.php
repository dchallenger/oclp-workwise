<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Letter_of_intent extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Letter Of Intent';
		$this->listview_description = 'This module lists all defined letter of intent(s).';
		$this->jqgrid_title = "letter of intent List";
		$this->detailview_title = 'Letter Of Intent Info';
		$this->detailview_description = 'This page shows detailed information about a particular letter of intent.';
		$this->editview_title = 'Letter Of Intent Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about letter of intent(s).';


		
		if(!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['approve'] != 1){
            $this->filter = $this->module_table.".employee_id = {$this->user->user_id}";
        }

        if(!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['approve'] == 1 && $this->user_access[$this->module_id]['post'] != 1 ){
            $this->filter = $this->module_table.".employee_id = {$this->user->user_id} OR ".$this->db->dbprefix.$this->module_table.'.approver LIKE "'.$this->userinfo['user_id'].'" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "%,'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "%,'.$this->userinfo['user_id'].'"';
        }

    	if( $this->input->post('filter') && $this->input->post('filter') == "all" ){
            if(!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['approve'] == 1 && $this->user_access[$this->module_id]['post'] != 1){
	            $this->filter = $this->module_table.".employee_id = {$this->user->user_id} OR ".$this->db->dbprefix.$this->module_table.'.approver LIKE "'.$this->userinfo['user_id'].'" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "%,'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "%,'.$this->userinfo['user_id'].'"';
	        }
        }

        if( $this->input->post('filter') && $this->input->post('filter') == "for_approval" ){
        	if($this->user_access[$this->module_id]['post'] != 1){
            	$this->filter = $this->db->dbprefix.$this->module_table.".status_id = 2 AND ".$this->db->dbprefix.$this->module_table.'.approver LIKE "'.$this->userinfo['user_id'].'" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "%,'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "%,'.$this->userinfo['user_id'].'"';  
        	}
        	else{
        		$this->filter = $this->db->dbprefix.$this->module_table.".status_id = 2";
        	}
        }

        if( $this->input->post('filter') && $this->input->post('filter') == "approved" ){
        	if($this->user_access[$this->module_id]['post'] != 1){
            	$this->filter = $this->db->dbprefix.$this->module_table.".status_id = 3 AND ".$this->db->dbprefix.$this->module_table.'.approver LIKE "'.$this->userinfo['user_id'].'" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "%,'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "%,'.$this->userinfo['user_id'].'"';  
        	}
        	else{
        		$this->filter = $this->db->dbprefix.$this->module_table.".status_id = 3";
        	}
        }

        if( $this->input->post('filter') && $this->input->post('filter') == "declined" ){
        	if($this->user_access[$this->module_id]['post'] != 1){
            	$this->filter = $this->db->dbprefix.$this->module_table.".status_id = 4 AND ".$this->db->dbprefix.$this->module_table.'.approver LIKE "'.$this->userinfo['user_id'].'" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "%,'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "%,'.$this->userinfo['user_id'].'"';  
        	}
        	else{
        		$this->filter = $this->db->dbprefix.$this->module_table.".status_id = 4";
        	}
        }

        if( $this->input->post('filter') && $this->input->post('filter') == "personal" ){
            $this->filter = $this->module_table.".employee_id = {$this->user->user_id}";    
        }

		

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


		//Tabs for Listview
        $tabs = array();
        if( ( $this->is_superadmin || $this->is_admin ) ||  $this->user_access[$this->module_id]['approve'] == 1 ){
            $data['filter'] = 'all';
            $tabs[] = '<li id="all" class="active" filter="all"><a href="javascript:void(0)">All</li>';
            $tabs[] = '<li id="personal" filter="personal"><a href="javascript:void(0)">Personal</li>';   
        }
        else{
            $data['filter'] = 'personal';
            $tabs[] = '<li id="personal" class="active" filter="personal"><a href="javascript:void(0)">Personal</li>';
        }

        if( $this->user_access[$this->module_id]['approve'] == 1 && $this->user_access[$this->module_id]['decline'] == 1 ){
        	$this->db->where($this->db->dbprefix.$this->module_table.".status_id = 2");

        	if( $this->user_access[$this->module_id]['post'] != 1 ){

        		$this->db->where($this->db->dbprefix.$this->module_table.'.approver LIKE "'.$this->userinfo['user_id'].'" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "%,'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.$this->module_table.'.approver LIKE "%,'.$this->userinfo['user_id'].'"');

        	}

        	$this->db->where('deleted = 0');

        	$for_approval = $this->db->get($this->module_table);

        	if( $for_approval->num_rows() > 0 ){
	        	$approval_counter = '<span class="bg-orange ctr-inline">'.$for_approval->num_rows().'</span>';
	    	}
	    	else{
	    		$approval_counter = '';

	    	}

	        $tabs[] = '<li  id="for_approval" filter="for_approval"><a href="javascript:void(0)">For Approval '. $approval_counter .'</li>';
	        if( $this->user_access[$this->module_id]['approve'] == 1 ){
	        	$tabs[] = '<li id="approved" filter="approved"><a href="javascript:void(0)">Approved</li>';
	    	}
	    	if( $this->user_access[$this->module_id]['decline'] == 1 ){
	        	$tabs[] = '<li id="declined" filter="declined"><a href="javascript:void(0)">Declined</li>';
	    	}
       	} 
        

        if( sizeof( $tabs ) > 1 ) $data['tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');
		


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

		$data['buttons'] = $this->module_link . '/view-buttons';

		//get current record

		$intent = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val))->row();
		$data['status'] = $intent->status_id;
		$data['employee_id'] = $intent->employee_id;
		$data['approver'] = explode(',',$intent->approver);
		
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
			$data['content'] = 'editview';
			
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			if( $this->input->post('record_id') == "-1" ){
				$data['status'] = -1;
			}
			else{
				$intent = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val))->row();
				$data['status'] = $intent->status_id;
			}


			$data['buttons'] = $this->module_link . '/edit-employee-buttons';
			
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


		if( $this->input->post('send_request') && $this->input->post('send_request') == "true" ){
			$_POST['status_id'] = 2;
		}
		else if( $this->input->post('approve') && $this->input->post('approve') == "true" ){
			$_POST['status_id'] = 3;
		}
		else if( $this->input->post('decline') && $this->input->post('decline') == "true" ){
			$_POST['status_id'] = 4;
		}
		else{
			$_POST['status_id'] = 1;
		}

		parent::ajax_save();

		if ($this->input->post('record_id') == '-1') {
			$this->db->update($this->module_table, array('date_created' => date('Y-m-d H:i:s'), 'deleted' => 0 ), array($this->key_field => $this->key_field_val));
		}

		if( $this->input->post('send_request') && $this->input->post('send_request') == "true" ){
			$approvers = $this->system->get_approvers($this->userinfo['position_id'], $this->module_id);
			$approver_array = array();

			foreach($approvers as $approver){
				array_push($approver_array, $approver['approver_position_id']);
			}

			$approver_list = implode(',', $approver_array);
			$this->db->update('recruitment_letter_of_intent', array('approver' => $approver_list, 'date_sent' => date('Y-m-d H:i:s')), array($this->key_field => $this->key_field_val));
		}

		if( ( $this->input->post('approve') && $this->input->post('approve') == "true" ) || (  $this->input->post('decline') && $this->input->post('decline') == "true"  ) ){
			$this->db->update('recruitment_letter_of_intent', array('date_approved' => date('Y-m-d H:i:s')), array($this->key_field => $this->key_field_val));
		}


		//additional module save routine here
				
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}

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
		if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

		//get list
		$result = $this->db->get();

		//$response->last_query = $this->db->last_query();

		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{
			$total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
			$response->page = $page > $total_pages ? $total_pages : $page;
			$response->total = $total_pages;
			$response->records = $result->num_rows();

			/* record query */
			//build query
			$this->_set_left_join();
			$this->db->select($this->listview_qry, false);
			$this->db->from($this->module_table);

			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );


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
					$columns_data = $result->field_data();
					$column_type = array();
					foreach($columns_data as $column_data){
						$column_type[$column_data->name] = $column_data->type;
					}
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
								if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33, 35, 36, 37, 39) ) ){
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

	// END - default module functions
	
	// START custom module funtions
	function get_position(){
		if(!IS_AJAX){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		$response = $this->hdicore->_get_userinfo( $this->input->post('user_id') );
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}



	function get_user_via_position(){
		if(!IS_AJAX){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
		
		$qry = "select a.user_id
		FROM {$this->db->dbprefix}user a
		LEFT JOIN {$this->db->dbprefix}employee b on b.user_id = a.user_id
		WHERE a.deleted = 0 AND a.inactive = 0 AND a.position_id = {$this->input->post('position_id')}
		AND b.resigned = 0 and b.resigned_date is null";
		$users = $this->db->query( $qry )->row();

		$response = $users;
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);	
	}

	function change_status($record_id = 0) {
        if ($this->input->post('record_id')) {
            $record_id = $this->input->post('record_id');
        }

        $this->db->where($this->key_field, $record_id);
        $result = $this->db->get($this->module_table);
        $request = $result->row_array();

        $approver = explode(',',$request['approver']);

        // Check if current user is part of approvers.
        if ( IS_AJAX ) {

        	if( in_array($this->userinfo['user_id'],$approver) ){


        		$this->load->helper('date');
                
                switch( $this->input->post('form_status_id') ){
                    case 3:
                        $returnstatus = 'approved';
                        break;
                    case 4: 
                        $returnstatus = 'declined';
                        break;
                }

                $response['message'] = 'Request ' . $returnstatus;

                switch( $this->input->post('form_status_id') ){
                    case 3: 
                        $data['status_id'] = 3;
                        $data['date_approved'] = date('Y-m-d H:i:s', now());
                        $this->db->where($this->key_field, $record_id);
                        $this->db->update($this->module_table, $data);
                    break;
                	case 4: 
                        $data['status_id'] = 4;
                        $data['date_approved'] = date('Y-m-d H:i:s', now());
                        $this->db->where($this->key_field, $record_id);
                        $this->db->update($this->module_table, $data);
                    break;
                }


                	$this->db->select('user.firstname, user.lastname, user.middlename, user.email, recruitment_letter_of_intent.mrf_id, recruitment_letter_of_intent.message, user_position.position, recruitment_letter_of_intent.date_created, recruitment_manpower.date_needed');
                	$this->db->where('recruitment_letter_of_intent.intent_id',$record_id);
                	$this->db->join('user','user.user_id = recruitment_letter_of_intent.employee_id','left');
                	$this->db->join('recruitment_manpower','recruitment_manpower.request_id = recruitment_letter_of_intent.mrf_id','left');
                	$this->db->join('user_position','user_position.position_id = recruitment_manpower.request_id','left');
                	
					$intent_result = $this->db->get('recruitment_letter_of_intent')->row();


					$response['last_query'] = $this->db->last_query();

					$email_data = array(
						'firstname' => $intent_result->firstname,
						'lastname' => $intent_result->lastname,
						'middlename' => $intent_result->middlename,
						'mrf_id' => $intent_result->mrfid,
						'message' => $intent_result->message,
						'applied_position' => $intent_result->position,
						'date_created' => date($this->config->item('display_date_format_email'),strtotime($intent_result->date_created)),
						'date_needed' => date($this->config->item('display_date_format_email'),strtotime($intent_result->date_needed)),
						'status' => $returnstatus
					);


					$this->load->model('template');
	                $template = $this->template->get_module_template(223, 'intent_status_employee');
	                $message = $this->template->prep_message($template['body'], $email_data);
	                $recepients[] = $intent_result->email;
	                $this->template->queue(implode(',', $recepients), '', $template['subject']." : ".$intent_result->firstname." ".$intent_result->lastname, $message);
	                

	                //Get recepients
		            $this->db->select('email, CONCAT( firstname, " ", lastname ) as name',FALSE);
		            $this->db->where('user_id',2); // HR Admin
		         	$recepient_result = $this->db->get('user');

		         	foreach( $recepient_result->result_array() as $recepients  ){

		         		$email_data['approver'] = $recepients['name'];
		         		$template = $this->template->get_module_template(223, 'intent_status_hr');
	                	$message = $this->template->prep_message($template['body'], $email_data);
	                	$this->template->queue($recepients['email'], '', $template['subject']." : ".$intent_result->firstname." ".$intent_result->lastname, $message);

		         	}


                            
                $response['type'] = 'success';

                $where = array(
					'deleted' => 0
				);
				$this->db->where( $where );
                $this->db->where('status_id', '2');
                $this->db->where('( approver LIKE "%,'.$this->userinfo['user_id'].'" OR approver LIKE "%,'.$this->userinfo['user_id'].',%" OR approver LIKE "'.$this->userinfo['user_id'].',%" OR approver LIKE "'.$this->userinfo['user_id'].'" )');
        		$result = $this->db->get($this->module_table);

        		$response['num_rows'] = $result->num_rows();
                
        	}
        	else{

        		$response['type'] = 'error';
                $response['message'] = 'You do not have sufficient privilege to execute this operation.<br/>Please call the Administrator.';  
        	}

        	$data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }


	function _default_grid_actions($module_link = "", $container = "", $row = array()) {
        $rec = $this->db->get_where( $this->module_table, array( $this->key_field => $row[$this->key_field] ) )->row();
        $approver = explode(',',$rec->approver);

        // set default
        if ($module_link == "")
            $module_link = $this->module_link;
        if ($container == "")
            $container = "jqgridcontainer";

        // Right align action buttons.
        $actions = '<span class="icon-group">';

        $approvers = $this->system->get_approvers_and_condition($rec->employee_id,$this->module_id);

        $approver_array = array();

        foreach($approvers as $approver){
            array_push($approver_array, $approver['approver']);
        }

        if ($this->user_access[$this->module_id]['approve']  && ( $rec->status_id == 2  ) && ( in_array($this->userinfo['user_id'],$approver_array) ) ) {
            $actions .= '<a class="icon-button icon-16-approve approve-single" tooltip="Approve" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
        }

        if ( $this->user_access[$this->module_id]['decline'] && ( $rec->status_id == 2  ) && ( in_array($this->userinfo['user_id'],$approver_array) ) ) {
            $actions .= '<a class="icon-button icon-16-cancel decline-single" tooltip="Decline" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
        }

       // if ( $this->_can_cancel( $rec ) && $rec->employee_id != $this->user->user_id ) {
       //     $actions .= '<a class="icon-button icon-16-cancel cancel-single" tooltip="Cancel" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
      //  }

        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="' . $module_link . '" tooltip="View" href="javascript:void(0)"></a>';
        }

        if ($this->user_access[$this->module_id]['print']) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
        }
        if ($this->user_access[$this->module_id]['edit'] && $rec->employee_id == $this->user->user_id && $rec->status_id == 1) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
        }

        if ($this->user_access[$this->module_id]['delete'] && $rec->employee_id == $this->user->user_id && $rec->status_id == 1 ) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

        return $actions;
    }

    function get_template_form(){

		$this->db->where('recruitment_letter_of_intent.employee_id',$this->userinfo['user_id']);
		$this->db->where('recruitment_letter_of_intent.mrf_id',$this->input->post('mrfid'));
		$this->db->where('recruitment_letter_of_intent.status_id',2);
		$intent_result = $this->db->get('recruitment_letter_of_intent');

		if( $intent_result->num_rows == 0 ){

			$sql = 'SELECT '. $this->db->dbprefix .'recruitment_manpower.request_id, '. $this->db->dbprefix .'recruitment_manpower.requested_date, '. $this->db->dbprefix .'recruitment_manpower.requested_by, '. $this->db->dbprefix .'recruitment_manpower.position_id, '. $this->db->dbprefix .'user_position.position, '. $this->db->dbprefix .'recruitment_manpower.date_needed, '. $this->db->dbprefix .'recruitment_manpower.status, '. $this->db->dbprefix .'recruitment_manpower.document_number, '. $this->db->dbprefix .'recruitment_manpower.status, '. $this->db->dbprefix .'recruitment_manpower.approved_by, '. $this->db->dbprefix .'recruitment_manpower.requested_by as rb_id, CONCAT(rb.firstname, " ", rb.lastname) as requested_by, concurred_as_approver, concurred_by, concurred_approved, approver_approved, concurred_optional, '. $this->db->dbprefix .'recruitment_manpower.created_by, '. $this->db->dbprefix .'recruitment_manpower.duties, '. $this->db->dbprefix .'recruitment_manpower.qualification, '. $this->db->dbprefix .'recruitment_manpower.attachment, job_description_attachment
					FROM (`'. $this->db->dbprefix .'recruitment_manpower`)
					LEFT JOIN `'. $this->db->dbprefix .'user` rb ON `rb`.`user_id` = `'. $this->db->dbprefix .'recruitment_manpower`.`requested_by`
					LEFT JOIN '. $this->db->dbprefix .'user_position ON '. $this->db->dbprefix .'user_position.position_id = '. $this->db->dbprefix .'recruitment_manpower.position_id
					WHERE `'. $this->db->dbprefix .'recruitment_manpower`.`deleted` = 0 AND 1 AND ( '. $this->db->dbprefix .'recruitment_manpower.status = "Approved" OR '. $this->db->dbprefix .'recruitment_manpower.status = "In-Process" )
					AND '. $this->db->dbprefix .'recruitment_manpower.request_id = '.$this->input->post('mrfid').'
					ORDER BY `request_id` desc, `'. $this->db->dbprefix .'recruitment_manpower`.`requested_date` asc';

			$result = $this->db->query($sql);

			if( $result->num_rows > 0 ){

				$response->form = $this->load->view( $this->userinfo['rtheme'].'/employee/letter_of_intent/template_form',array('manpower' => $result->row()), true );
				$this->load->view('template/ajax', array('json' => $response));

			}

		}
		else{

			$response->msg = "You can only submit one Letter of Intent per request.";
	        $response->msg_type = "error";
	        $data['json'] = $response;

	        $this->load->view('template/ajax', array('json' => $response));

		}

	}

	function send_letter_of_intent(){

		if (IS_AJAX) {
			
			$approvers = $this->system->get_approvers_and_condition($this->userinfo['user_id'],223);

			if( !empty($approvers) ){

	            $approver_array = array();

	            foreach($approvers as $approver){
	                array_push($approver_array, $approver['approver']);
	            }
				
				$data = array(
					'employee_id' => $this->userinfo['user_id'],
					'mrf_id' => $this->input->post('mrfid'),
					'message' => $this->input->post('message'),
					'status_id' => 2,
					'date_created' => date('Y-m-d h:i:s'),
					'date_sent' => date('Y-m-d h:i:s'),
					'approver' => implode(',',$approver_array)
				);

				 $result = $this->db->insert('recruitment_letter_of_intent', $data);
				$result = true;
				if($result){

					$sql = 'SELECT '. $this->db->dbprefix .'recruitment_manpower.request_id, '. $this->db->dbprefix .'recruitment_manpower.requested_date, '. $this->db->dbprefix .'recruitment_manpower.requested_by, '. $this->db->dbprefix .'recruitment_manpower.position_id, '. $this->db->dbprefix .'user_position.position, '. $this->db->dbprefix .'recruitment_manpower.date_needed, '. $this->db->dbprefix .'recruitment_manpower.status, '. $this->db->dbprefix .'recruitment_manpower.document_number, '. $this->db->dbprefix .'recruitment_manpower.status, '. $this->db->dbprefix .'recruitment_manpower.approved_by, '. $this->db->dbprefix .'recruitment_manpower.requested_by as rb_id, CONCAT(rb.firstname, " ", rb.lastname) as requested_by, concurred_as_approver, concurred_by, concurred_approved, approver_approved, concurred_optional, '. $this->db->dbprefix .'recruitment_manpower.created_by, '. $this->db->dbprefix .'recruitment_manpower.duties, '. $this->db->dbprefix .'recruitment_manpower.qualification
					FROM (`'. $this->db->dbprefix .'recruitment_manpower`)
					LEFT JOIN `'. $this->db->dbprefix .'user` rb ON `rb`.`user_id` = `'. $this->db->dbprefix .'recruitment_manpower`.`requested_by`
					LEFT JOIN '. $this->db->dbprefix .'user_position ON '. $this->db->dbprefix .'user_position.position_id = '. $this->db->dbprefix .'recruitment_manpower.position_id
					WHERE `'. $this->db->dbprefix .'recruitment_manpower`.`deleted` = 0 AND 1 AND ( '. $this->db->dbprefix .'recruitment_manpower.status = "Approved" OR '. $this->db->dbprefix .'recruitment_manpower.status = "In-Process" )
					AND '. $this->db->dbprefix .'recruitment_manpower.request_id = '.$this->input->post('mrfid').'
					ORDER BY `request_id` desc, `'. $this->db->dbprefix .'recruitment_manpower`.`requested_date` asc';

					$manpower_result = $this->db->query($sql)->row();

					$email_data = array(
						'firstname' => $this->userinfo['firstname'],
						'lastname' => $this->userinfo['lastname'],
						'middlename' => $this->userinfo['middlename'],
						'mrf_id' => $this->input->post('mrfid'),
						'message' => $this->input->post('message'),
						'applied_position' => $manpower_result->position,
						'date_created' => date($this->config->item('display_date_format_email')),
						'date_needed' => date($this->config->item('display_date_format_email'),strtotime($manpower_result->date_needed))
					);

					$this->load->model('template');

					/* disable sending email to user
	                $template = $this->template->get_module_template(223, 'intent_send_email');
	                $message = $this->template->prep_message($template['body'], $email_data);
	                $recepients[] = $this->userinfo['email'];
	                $this->template->queue(implode(',', $recepients), '', $template['subject']." : ".$this->userinfo['firstname']." ".$this->userinfo['lastname'], $message);
	                */

	                //Get recepients
		            $this->db->select('email, CONCAT( firstname, " ", lastname ) as name',FALSE);
		            $this->db->where_in('user_id',$approver_array);
		            $this->db->or_where('user_id',2); // HR Admin
		         	$recepient_result = $this->db->get('user');

		         	foreach( $recepient_result->result_array() as $recepients  ){

		         		$email_data['approver'] = $recepients['name'];
		         		$template = $this->template->get_module_template(223, 'intent_received_email');
	                	$message = $this->template->prep_message($template['body'], $email_data);
	                	$this->template->queue($recepients['email'], '', $template['subject']." : ".$this->userinfo['firstname']." ".$this->userinfo['lastname'], $message);

		         	}

					$response->msg = "Letter of Intent is Successfully Submitted.";
		            $response->msg_type = "success";
		            $data['json'] = $response;

	         	}
	         	else{

	         		$response->msg = "There is an error in submitting the Letter of Intent.";
		            $response->msg_type = "error";
		            $data['json'] = $response;

	         	}

         	}
         	else{

         		$response->msg = "No approver has been set";
		        $response->msg_type = "error";
		        $data['json'] = $response;

         	}

         	$this->load->view('template/ajax', array('json' => $response));

		}		
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>