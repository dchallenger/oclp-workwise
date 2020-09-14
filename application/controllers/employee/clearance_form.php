<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Clearance_form extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists clearance forms.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a clearance form.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about a clearance form.';
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
		
		$this->db->where($this->key_field, $this->input->post('record_id'));
		$this->db->where('deleted', 0);

		$record = $this->db->get($this->module_table)->row_array();
		$data['record'] = $record;

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
		if($this->user_access[$this->module_id]['edit'] == 1) {
			if ($this->input->post('employee_clearance_id') != '' && $this->input->post('record_id') == '') {
				$this->db->where('employee_clearance_id', $this->input->post('employee_clearance_id'));
				$this->db->where('deleted', 0);

				$result = $this->db->get($this->module_table);

				if ($result->num_rows() == 0) {
					$_POST['record_id'] = '-1';
				} else {
					$_POST['record_id'] = $result->row()->{$this->key_field};
				}

				$employee_clearance_id = $this->input->post('employee_clearance_id');
			}

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
			$data['buttons'] = $this->module_link . '/edit-buttons';

			if ($this->input->post('record_id') != '-1') {
				$this->db->where($this->key_field, $this->input->post('record_id'));
				$this->db->where('deleted', 0);

				$record = $this->db->get($this->module_table)->row_array();
				$data['record'] = $record;

				$data['status_data'] = unserialize($record['status']);

				$employee_clearance_id = $record['employee_clearance_id'];
			}			

			$data['checklist'] = $this->_get_form_checklist($employee_clearance_id);
			$data['employee_id'] = $this->input->post('employee_id');

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
        $this->db->where('employee_clearance_id', $this->input->post('employee_clearance_id'));
        $this->db->join('user','user.employee_id = employee_clearance.employee_id');
        $record = $this->db->get('employee_clearance')->row();

        if ($record){
			$employee_id = $record->employee_id;
			$effectivity_year = date('Y');

			$this->db->where('employee_appraisal_bsc.deleted',0);
			$this->db->where('status <',3);
			$this->db->where(array("appraiser_id"=>$employee_id,"appraisal_year"=>$effectivity_year));
			$this->db->join('employee_appraisal_period', 'employee_appraisal_period.employee_appraisal_period_id = employee_appraisal_bsc.appraisal_period_id');
			$this->db->join('user', 'user.user_id = employee_appraisal_bsc.employee_id');
			$result = $this->db->get('employee_appraisal_bsc');

	        if ($result->num_rows() > 0):                    
	            $response->msg = "".$record->firstname." ".$record->lastname." has a pending PA to approve.";
	            $response->msg_type = "error";
	            $data['json'] = $response;
	            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	        else:
	            parent::ajax_save();
	        endif;
        }		
        else{
			parent::ajax_save();        	
        }
		//additional module save routine here

	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions

	 function after_ajax_save()
    {
        if ($this->get_msg_type() == 'success') {
            $ratings = $this->input->post('a_status');

            $this->db->select('*, employee_clearance.status as clearance_status');
            $this->db->join('employee_clearance','employee_clearance.employee_clearance_id = employee_clearance_form.employee_clearance_id','left');
            $this->db->where($this->key_field, $this->key_field_val);
            $record = $this->db->get($this->module_table)->row();

            $status_ctr = 0;

            if ($record->status != '') {
                $status = unserialize($record->status);
                // Loop through original values so we don't overwrite
                // @TODO: compare against employee_clearance_form_checklist approvers               
                foreach ($ratings as $i => $rating) {
                    $status[$i] = $rating;

                    //check if all status of each signatories are already approved
                    if( ( $i == 'status' ) ){
                        foreach( $rating as $signatory_status ){
                            if( $signatory_status == 0 ){

                                $status_ctr++;

                            }
                        }
                    }

                }

            } else {
                $status = $ratings;
            }

	        $this->db->where($this->key_field, $this->key_field_val);
            $this->db->update($this->module_table,  array('status' => serialize($status)));
            if( $status_ctr == 0 ){
            	$this->db->update('employee_clearance', array('status' => 2, 'date_approved' => date('Y-m-d') , 'quitclaim_received' => 1), array('employee_clearance_id' => $record->employee_clearance_id));
                 $this->db->update('employee', array('quitclaim_received' => 1), array('employee_id' => $record->employee_id));
            	// $this->db->update('employee_clearance', array('quitclaim_lastday_notification' => date('Y-m-d')), array($this->key_field => $this->key_field_val));


            }
            else{
            	if ($record->clearance_status !== '2') {
            		$this->db->update('employee_clearance', array('status' => 1 ), array('employee_clearance_id' => $record->employee_clearance_id));
            	}
               
            }

        }       

        parent::after_ajax_save();
    }

	private function _get_form_checklist($record_id)
	{		
		$this->db->where('employee_clearance.deleted', 0);
		$this->db->where('employee_clearance_id', $record_id);		
		
		$result = $this->db->get('employee_clearance');
		$employee_id = $result->row()->employee_id;
		$ret = false;

		if ($result->num_rows() > 0) {
/*			$this->db->where_in('employee_clearance_form_checklist_id', explode(',', $result->row()->signatories));
			$this->db->where('employee_clearance_form_checklist.deleted', 0);
			$this->db->join('user', 'user.user_id = employee_clearance_form_checklist.approver_id');

			if (!$this->user_access[$this->module_id]['post']) {
				$this->db->where('user.user_id', $this->userinfo['user_id']);
			}

			$ret = $this->db->get('employee_clearance_form_checklist');

			if ($ret->num_rows() == 0) {
				$ret = false;
			} else {
				$ret = $ret->result_array();
			}*/

			$sql = 'SELECT *,a.employee_clearance_form_checklist_id as ecfid FROM '.$this->db->dbprefix('employee_clearance_form_checklist').' a
							  JOIN '.$this->db->dbprefix('user').' b ON (b.user_id = a.approver_id)
							  LEFT JOIN (SELECT group_concat(equipment 	 separator ", ") AS equipment,employee_clearance_form_checklist_id FROM '.$this->db->dbprefix('employee_accountabilities').' WHERE employee_id = '.$employee_id.' GROUP BY employee_clearance_form_checklist_id) AS c ON (a.employee_clearance_form_checklist_id = c.employee_clearance_form_checklist_id)
							  WHERE a.employee_clearance_form_checklist_id IN ('.$result->row()->signatories.')';
/*			if (!$this->user_access[$this->module_id]['post']) {
				$sql .= ' AND b.user_id = '.$this->userinfo['user_id'].'';
			}*/
			$sql .= ' AND a.deleted =  0';

			$ret = $this->db->query($sql);

			if ($ret){
				if ($ret->num_rows() == 0) {
					$ret = false;
				} else {
					$ret = $ret->result_array();
				}							
			}
			else{
				$ret = false;				
			}
		}

		return $ret;		
	}

	function print_record() {

		$employee_id = $this->input->post('employee_id');
    	$record_id = $this->input->post('employee_clearance_id');

		$this->db->select('u.salutation, u.firstname, u.lastname, c.company, u.aux,  e.employed_date, u.middleinitial, p.position, d.department, ecs.status, e.resigned_date');
		$this->db->where('ec.employee_clearance_id',$record_id,false);
		$this->db->from('employee_clearance ec');
		$this->db->join('employee_clearance_form ecs','ecs.employee_clearance_id = ec.employee_clearance_id','left');
		$this->db->join('user u','u.employee_id = ec.employee_id','left');
		$this->db->join('employee e','e.employee_id = u.employee_id','left');
		$this->db->join('user_company_department d','d.department_id = u.department_id','left');
		$this->db->join('user_position p','p.position_id = u.position_id','left');
		$this->db->join('user_company c','c.company_id = u.company_id','left');
		$result = $this->db->get();

		$checklist = $this->_get_form_checklist($record_id);
		// dbug($checklist);

		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template', 'system'));

		$this->db->where('code', 'ecf');
		$this->db->where('deleted', 0);		
		$template = $this->db->get('template')->row();

		if( $result->num_rows() > 0 ) {
			$record = $result->row();
			$status = unserialize($record->status);
			$logo = get_branding();
			$user_info = $this->system->get_employee($employee_id);
			$company_id = $user_info['company_id'];
			$company_qry = $this->db->get_where('user_company', array('company_id' => $company_id))->row();
			if(!empty($company_qry->logo)) {
			  $logo = '<img alt="" src="'.base_url().''.$company_qry->logo.'">';
			}
			$image_table = '<table style="width:100%">'.$logo.'</table>';
			$table_detail = '<table border="0" style="align:center;width:100%;font-size:100px;">';
			$table_detail .= '<tr>';
			$table_detail .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>DEPARTMENT</strong></td>';
			$table_detail .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>ACCOUNTABILITIES</strong></td>';
			$table_detail .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>REMARKS</strong></td>';
			$table_detail .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>NAME & SIGNATURE</strong></td>';
			$table_detail .= '</tr>';
			$employee_name = $record->firstname." ".$record->middleinitial." ".$record->lastname ." ".$record->aux;
			$employee_dept = $record->department;
			$employee_position = $record->position;
			$effectivity_separation = ($record->resigned_date != '' &&  $record->resigned_date != '0000-00-00' ? date("F d, Y", strtotime($record->resigned_date)) : '');
			foreach ($checklist as $key => $value) {
				if(!empty($status)) {
					$accountabilities = $status['accountabilities'][$key+1];
					$comment = $status['comments'][$key+1];
				} else {
					$accountabilities = $value['equipment'];
					$comment = '';
				}

				$table_detail .= '<tr style="">';
				$table_detail .= '<td style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>';
				$table_detail .= '<td style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>';
				$table_detail .= '<td style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>';
				$table_detail .= '<td style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>';
				$table_detail .= '</tr>';
				$table_detail .= '<tr style="">';
				$table_detail .= '<td style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>';
				$table_detail .= '<td style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>';
				$table_detail .= '<td style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>';
				$table_detail .= '<td style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>';
				$table_detail .= '</tr>';
				$table_detail .= '<tr style="">';
				$table_detail .= '<td style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>';
				$table_detail .= '<td style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>';
				$table_detail .= '<td style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>';
				$table_detail .= '<td style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>';
				$table_detail .= '</tr>';
				$table_detail .= '<tr style="">';
				$table_detail .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.ucwords($value['description']).'</td>';
				$table_detail .= '<td style="text-align:left;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$accountabilities.'</td>';
				$table_detail .= '<td style="text-align:left;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$comment.'</td>';
				$table_detail .= '<td style="text-align:left;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;"><strong>'.$value['firstname'].' '.$value['lastname'].'</strong></td>';
				$table_detail .= '</tr>';
			}
			$table_detail .= '</table>';
			// dbug($table_detail);
			$vars = array(
				'image_table' => $image_table,
				'table_detail' => $table_detail,
				'employee_name' => $employee_name,
				'employee_dept' => $employee_dept,
				'employee_position' => $employee_position,
				'effectivity_separation' => $effectivity_separation,
			);
			$html = $this->template->prep_message($template->body, $vars, false, true);

			// Prepare and output the PDF.
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' Certificate of Employment' . '.pdf', 'D');
		}
	}
	// END custom module funtions
}

/* End of file */
/* Location: system/application */