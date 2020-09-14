<?php if (! defined('BASEPATH')) exit ('No direct script access allowed');

include (APPPATH . 'controllers/employee/employee_update.php');

class Firstbalfour_employee_update extends Employee_update{

 	function send_email() {

		// $approvers=$this->system->get_module_approver( $this->module_id, $this->userinfo['position_id'] );
 		
        $this->db->join('user','user.employee_id=employee_update.employee_id');
        if( ( !$this->is_admin && !$this->superadmin ) && ( $this->userinfo['user_id'] != 1 && $this->userinfo['user_id'] != 2 ) ) {
          $this->db->where('user.employee_id', $this->userinfo['user_id']); 
        }
        $this->db->where('employee_update_id',$this->input->post('record_id'));
        $request = $this->db->get('employee_update');
        
        if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {
            $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $request->row_array();

                $approvers=$this->system->get_approvers_emails_and_condition($request['user_id'], $this->module_id );

                // Load the template.            
                $this->load->model('template');
                $template = $this->template->get_module_template($this->module_id, 'update201');
                $request['date_sent'] = date( 'd M Y');  

                foreach($approvers as $approver)
                {
	                $this->db->where('user_id', $approver['approver']);
	                $emailApprover=$this->db->get('user')->result_array();
	                foreach ($emailApprover as $row_email) {
                        $request['approver_user'] = $row_email['salutation']." ".$row_email['firstname']." ". $row_email['lastname'];
                        if ($row_email['aux'] != ''){
                            $request['approver_user'] = $row_email['salutation']." ".$row_email['firstname']." " . $row_email['lastname']." ".$row_email['aux']."";
                        }                         
	                }

                    $message = $this->template->prep_message($template['body'], $request);  
                                      
                    if ($this->template->queue(trim($row_email['email']), '', $template['subject'], $message)) {
                        $data['employee_update_status_id'] = 1;
                        $data['email_sent'] = '1';
                        $data['date_sent'] = date('Y-m-d G:i:s');                    
                        $this->db->where($this->key_field, $request[$this->key_field]);
                        $this->db->update($this->module_table, $data);
                        $this->db->update('form_approver', array('status' => 2), array('module_id' => $this->module_id, 'record_id' => $this->input->post('record_id')) );
                    } else {
                        $data['employee_update_status_id'] = 1;
                        $this->db->where($this->key_field, $request[$this->key_field]);
                        $this->db->update($this->module_table, $data);
                    } 	                
	            }
            }
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }


    function ajax_save($bypass = false)
    {
        if ( !$this->user_access[$this->module_id]['post'] && $this->input->post('employee_id') != $this->userinfo['user_id']) {
            show_error('The action you have requested is not allowed.');        
        }

        if ($bypass) {my_controller::ajax_save(); return;}
        
        my_controller::ajax_save();
        //additional module save routine here

        //attachment
        $data = $this->_rebuild_array($this->input->post('attachment'), $this->input->post('employee_id'),'employee_update_id',$this->key_field_val);      
        if (count($data) > 0){
            $this->db->insert_batch('employee_update_attachment', $data);   
        }
        //end of attachment

        // if(count($this->input->post('family')) < 1){
        //  $this->db->set('employee_update_status_id', '2');
        //  $this->db->where('employee_update_id',$this->input->post('record_id'));
        //  $this->db->update('employee_update');
        // }
        //changes for bu
        $this->db->where('employee_update_id', $request_id);
        $this->db->where('deleted', 0);
        $check_if_changes = $this->db->get('employee_update');
        $check_if_changes_val = $check_if_changes->result_array();

        if ($check_if_changes->num_rows() != 0){
         $to_be_inserted=$this->db->dbprefix."employee";
         $to_be_inserted_2=$this->db->dbprefix."user";
         $insert_here=$this->db->dbprefix."employee_bu";

         $empid=$check_if_changes_val[0]['employee_id'];
         $update_id=$check_if_changes_val[0]['employee_update_id'];
         $this->db->query("INSERT INTO $insert_here(employee_id, employee_update_id, firstname , middlename , lastname , date_of_marriage , civil_status_id , mobile , home_phone , emergency_name , emergency_phone , emergency_relationship , emergency_address , pres_address1 , pres_address2 , pres_city , pres_province , pres_zipcode , perm_address1 , perm_address2 , perm_city , perm_province , perm_zipcode) SELECT $empid , $update_id , $to_be_inserted_2.firstname , $to_be_inserted_2.middlename , $to_be_inserted_2.lastname , $to_be_inserted_2.date_of_marriage , $to_be_inserted.civil_status_id , $to_be_inserted.mobile , $to_be_inserted.home_phone , $to_be_inserted.emergency_name , $to_be_inserted.emergency_phone , $to_be_inserted.emergency_relationship , $to_be_inserted.emergency_address , $to_be_inserted.pres_address1 , $to_be_inserted.pres_address2 , $to_be_inserted.pres_city , $to_be_inserted.pres_province , $to_be_inserted.pres_zipcode , $to_be_inserted.perm_address1 , $to_be_inserted.perm_address2 , $to_be_inserted.perm_city , $to_be_inserted.perm_province , $to_be_inserted.perm_zipcode FROM $to_be_inserted LEFT JOIN $to_be_inserted_2 ON $to_be_inserted.employee_id = $to_be_inserted_2.employee_id WHERE $to_be_inserted.employee_id=$empid");
        }
        //changes for bu

        $ctr=1;
        $flag=false;
        $empty=false;
        //if($this->input->post('record_id') == -1)
        if( $this->input->post('record_id') == -1 )
        {
            $query = $this->db->get_where("employee_update", array('employee_update_id' => $this->key_field_val));
            $row = $query->row_array(); 
            $flag=true;
        }

        // Changes for Personal. This is retained so that it automatically show changed edited
        if($flag){
            $this->db->set('employee_id', $this->input->post('employee_id'));
            $this->db->set('employee_update_id', $this->key_field_val);
            $this->db->set('personal_fName', $this->input->post('first_name'));
            $this->db->set('personal_mName', $this->input->post('middle_name'));
            $this->db->set('personal_lName', $this->input->post('last_name'));
            $this->db->set('personal_dom', date( 'Y-m-d', strtotime($this->input->post('date_of_marriage'))));
            $this->db->insert('employee_update_personal');
        }
        else{
            $this->db->set('personal_fName', $this->input->post('first_name'));
            $this->db->set('personal_mName', $this->input->post('middle_name'));
            $this->db->set('personal_lName', $this->input->post('last_name'));
            $this->db->set('personal_dom', date( 'Y-m-d', strtotime($this->input->post('date_of_marriage'))));
            $this->db->where('employee_update_id', $this->key_field_val);
            $this->db->update('employee_update_personal');
        }
        // Changes for Personal. This is retained so that it automatically show changed edited

        // This is where Family is saved


        foreach($this->input->post('family') as $valme)
        {
                if($ctr==1 && $valme!="") $this->db->set('name', $valme);
                elseif($ctr==1 && $valme=="")$empty=true;
                if($ctr==2) $this->db->set('relationship', $valme);
                if($ctr==3) $this->db->set('birthdate', date( 'Y-m-d', strtotime( $valme )));
                if($ctr==4) $this->db->set('occupation', $valme);
                if($ctr==5) $this->db->set('employer', $valme);

                //changes for add entry
                if($ctr==6) $this->db->set('educational_attainment', $valme);
                if($ctr==7) $this->db->set('degree', $valme);
                if($ctr==8 && $empty==false) $this->db->set('ecf_dependent', $valme);
                if($ctr==9 && $empty==false) $this->db->set('bir_dependent', $valme);
                if($ctr==10 && $empty==false) $this->db->set('hospitalization_dependent', $valme);
                //changes for add entry

                if($ctr==11) $this->db->set('already_exist', $valme);
                if($ctr==12) $this->db->set('flagcount', $valme);

            $ctr++;
            if($ctr % 13 == 0)
            {
                
                if($flag)
                {
                    if($empty==false){
                        $this->db->set('employee_update_id', $row['employee_update_id']);
                        $this->db->set('employee_id', $this->input->post('employee_id'));
                        $this->db->insert('employee_update_family');
                        $this->db->set('employee_update_status_id', '1');
                        $this->db->where('employee_update_id',$this->input->post('record_id'));
                        $this->db->update('employee_update');
                    } else 
                        $empty=false;
                }
                else
                {
                    if($empty==false){
                        //$this->db->set('employee_update_id', $this->input->post('record_id') );
                        $this->db->where('employee_update_id', $this->input->post('record_id'));
                        $this->db->where('flagcount',$valme);
                        $this->db->update('employee_update_family');
                        $this->db->set('employee_update_status_id', '1');
                        $this->db->where('employee_update_id',$this->input->post('record_id'));
                        $this->db->update('employee_update');
                    } else 
                        $empty = false;
                }
                $ctr=1;
            }
        }
        // This is where family is saved
    }

    function _rebuild_array($array, $fkey = null,$parent_id_name = false,$parent_id_val = false) {
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
                    $new_array[$index]['employee_id'] = $fkey;
                }
                if ($parent_id_name && $parent_id_val) {
                    $new_array[$index][$parent_id_name] = $parent_id_val;
                }               
            }

            $index++;
        }

        return $new_array;
    }

    function change_status($record_id = 0, $status = "", $non_ajax = 0) 
    {
        if( $non_ajax == 0 ){
            $status    = $this->input->post('status');
            $record_id = $this->input->post('record_id');
        }

        switch ($status) {
            case 'approve':                 
                    $data['employee_update_status_id'] = 2;
                    if (!$this->_approve_request($record_id)) {
                        $response->msg_type = 'error';
                        $response->msg      = 'Update failed. Contact the Administrator';
                    } else {
                        $this->db->where($this->key_field, $record_id);                     
                        $this->db->update($this->module_table, $data);

                        $response->msg_type = 'success';                        
                        $response->msg      = 'Employee 201 update request approved. Employee records updated.';
                    }                                   
                break;
            case 'decline':                 
                    $data['employee_update_status_id'] = 3;
                    $this->db->where($this->key_field, $record_id);
                    
                    if (!$this->db->update($this->module_table, $data)) {
                        $response->msg_type = 'error';
                        $response->msg      = 'Update failed. Contact the Administrator';
                    } else {
                        $response->msg_type = 'success';
                        $response->msg      = 'Employee 201 update request denied.';
                    }
            break;
        }

        if($response->msg_type == 'success')
            $this->_send_status_email($record_id);

        if( $non_ajax == 0){
            // if($this->input->post('bypass'))
                $this->load->view('template/ajax', array('json' => $response));
            // else
            //  $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $response);
        }
        else{
            return $response;
        }
    }

    protected function _approve_request($request_id = 0)
    {


        //changes for bu
        // $this->db->where('employee_update_id', $request_id);
        // $this->db->where('deleted', 0);
        // $check_if_changes = $this->db->get('employee_update');
        // $check_if_changes_val = $check_if_changes->result_array();

        // if ($check_if_changes->num_rows() != 0){
        //  $to_be_inserted=$this->db->dbprefix."employee";
        //  $to_be_inserted_2=$this->db->dbprefix."user";
        //  $insert_here=$this->db->dbprefix."employee_bu";

        //  $empid=$check_if_changes_val[0]['employee_id'];
        //  $update_id=$check_if_changes_val[0]['employee_update_id'];
        //  $this->db->query("INSERT INTO $insert_here(employee_id, employee_update_id, firstname , middlename , lastname , date_of_marriage , civil_status_id , mobile , home_phone , emergency_name , emergency_phone , emergency_relationship , emergency_address , pres_address1 , pres_address2 , pres_city , pres_province , pres_zipcode , perm_address1 , perm_address2 , perm_city , perm_province , perm_zipcode) SELECT $empid , $update_id , $to_be_inserted_2.firstname , $to_be_inserted_2.middlename , $to_be_inserted_2.lastname , $to_be_inserted_2.date_of_marriage , $to_be_inserted.civil_status_id , $to_be_inserted.mobile , $to_be_inserted.home_phone , $to_be_inserted.emergency_name , $to_be_inserted.emergency_phone , $to_be_inserted.emergency_relationship , $to_be_inserted.emergency_address , $to_be_inserted.pres_address1 , $to_be_inserted.pres_address2 , $to_be_inserted.pres_city , $to_be_inserted.pres_province , $to_be_inserted.pres_zipcode , $to_be_inserted.perm_address1 , $to_be_inserted.perm_address2 , $to_be_inserted.perm_city , $to_be_inserted.perm_province , $to_be_inserted.perm_zipcode FROM $to_be_inserted LEFT JOIN $to_be_inserted_2 ON $to_be_inserted.employee_id = $to_be_inserted_2.employee_id WHERE $to_be_inserted.employee_id=$empid");
        // }
        //changes for bu

        // for whatever purpose it may serve family_bu
        $this->db->where('employee_update_id', $request_id);
        $this->db->where('deleted', 0);
        $check_if_changes = $this->db->get('employee_update');
        $check_if_changes_val = $check_if_changes->result_array();

        if ($check_if_changes->num_rows() != 0){
            $to_be_inserted=$this->db->dbprefix."employee";
            $to_be_inserted_2=$this->db->dbprefix."user";
            $insert_here=$this->db->dbprefix."employee_bu";

            $empid=$check_if_changes_val[0]['employee_id'];
            $update_id=$check_if_changes_val[0]['employee_update_id'];
        }
        // for whatever purpose it may serve family_bu

        $this->db->where($this->module_table.'.'.$this->key_field, $request_id);
        $this->db->where($this->module_table.'.deleted', 0);
        $this->db->join('employee_update_personal','employee_update_personal.employee_update_id = '.$this->module_table.'.employee_update_id');
        $request = $this->db->get($this->module_table);

        if (!$request && $request->num_rows() == 0) {
            return false;
        } else {
            foreach ($request->row() as $field => $value) {
                if ($field != 'date_created' && trim($value) != '') {
                    $data[$field] = $value;
                }
            }

            if (count($data) > 0) {
                $update_request_fields = array_keys($data);
                $employee_fields       = $this->db->list_fields('employee');

                $available_fields = array_intersect($update_request_fields, $employee_fields);
                $update_fields    = array();
                foreach ($available_fields as $field) {
                    $update_fields[$field] = $data[$field];
                }

                //$this->db->where('employee_id', $request->row()->employee_id);
                //$this->db->update('employee', $update_fields);

                $user_fields       = $this->db->list_fields('user');

                $available_fields = array_intersect($update_request_fields, $user_fields);

                $update_fields    = array();
                foreach ($available_fields as $field) {
                    $update_fields[$field] = $data[$field];
                }

                if ($data['personal_dom'] != '' && $data['personal_dom'] != 'NULL' && $data['personal_dom'] != "1970-01-01"){
                    $update_fields['date_of_marriage'] = $data['personal_dom'];
                }

                if ($data['personal_mName'] != '' && $data['personal_mName'] != 'NULL'){
                    $update_fields['middlename'] = $data['personal_mName'];
                    $update_fields['middleinitial'] = strtoupper(substr($data['personal_mName'], -1)).'.';
                }

                if ($data['personal_lName'] != '' && $data['personal_lName'] != 'NULL'){
                    $update_fields['lastname'] = $data['personal_lName'];
                }

                if ($data['personal_fName'] != '' && $data['personal_fName'] != 'NULL'){
                    $update_fields['firstname'] = $data['personal_fName'];
                }

                $this->db->where('employee_id', $request->row()->employee_id);
                $this->db->update('user', $update_fields);      


                if ($data['mobile'] != '' && $data['mobile'] != 'NULL'){
                    $update_employee_fields['mobile'] = $data['mobile'];
                }

                if ($data['home_phone'] != '' && $data['home_phone'] != 'NULL'){
                    $update_employee_fields['home_phone'] = $data['home_phone'];
                }

                if ($data['emergency_name'] != '' && $data['emergency_name'] != 'NULL'){
                    $update_employee_fields['emergency_name'] = $data['emergency_name'];
                }

                if ($data['emergency_phone'] != '' && $data['emergency_phone'] != 'NULL'){
                    $update_employee_fields['emergency_phone'] = $data['emergency_phone'];
                }

                if ($data['emergency_relationship'] != '' && $data['emergency_relationship'] != 'NULL'){
                    $update_employee_fields['emergency_relationship'] = $data['emergency_relationship'];
                }

                if ($data['emergency_address'] != '' && $data['emergency_address'] != 'NULL'){
                    $update_employee_fields['emergency_address'] = $data['emergency_address'];
                }

                if ($data['pres_address1'] != '' && $data['pres_address1'] != 'NULL'){
                    $update_employee_fields['pres_address1'] = $data['pres_address1'];
                }

                if ($data['pres_address2'] != '' && $data['pres_address2'] != 'NULL'){
                    $update_employee_fields['pres_address2'] = $data['pres_address2'];
                }

                if ($data['pres_city'] != '' && $data['pres_city'] != 'NULL'){
                    $update_employee_fields['pres_city'] = $data['pres_city'];
                }

                if ($data['pres_province'] != '' && $data['pres_province'] != 'NULL'){
                    $update_employee_fields['pres_province'] = $data['pres_province'];
                }

                if ($data['pres_zipcode'] != '' && $data['pres_zipcode'] != 'NULL'){
                    $update_employee_fields['pres_zipcode'] = $data['pres_zipcode'];
                }

                if ($data['perm_address1'] != '' && $data['perm_address1'] != 'NULL'){
                    $update_employee_fields['perm_address1'] = $data['perm_address1'];
                }

                if ($data['perm_address2'] != '' && $data['perm_address2'] != 'NULL'){
                    $update_employee_fields['perm_address2'] = $data['perm_address2'];
                }

                if ($data['perm_city'] != '' && $data['perm_city'] != 'NULL'){
                    $update_employee_fields['perm_city'] = $data['perm_city'];
                }

                if ($data['perm_province'] != '' && $data['perm_province'] != 'NULL'){
                    $update_employee_fields['perm_province'] = $data['perm_province'];
                }

                if ($data['perm_zipcode'] != '' && $data['perm_zipcode'] != 'NULL'){
                    $update_employee_fields['perm_zipcode'] = $data['perm_zipcode'];
                }


                $this->db->where('employee_id', $request->row()->employee_id);
                $this->db->update('employee', $update_employee_fields);  

            }

            
            //updates attachment
            $this->db->where('employee_update_id', $request_id);
            $this->db->where('deleted', 0);
            $update_201_attachment_result = $this->db->get('employee_update_attachment');           
            if ($update_201_attachment_result && $update_201_attachment_result->num_rows() > 0){
                foreach ($update_201_attachment_result->result_array() as $row_array) {
                    unset($row_array['attachment_id']);
                    unset($row_array['employee_update_id']);
                    unset($row_array['date_created']);
                    unset($row_array['deleted']);

                    $this->db->insert('employee_attachment',$row_array);
                }
            }
            //end updates attachment

            //updates
            $this->db->where('employee_update_id', $request_id);
            $this->db->where('deleted', 0);
            $updated_family = $this->db->get('employee_update_family');

            if (!$updated_family && $updated_family->num_rows() == 0) {
            } else {
                $updated_family_val = $updated_family->result_array();

                // $table=$this->db->dbprefix."employee_update_family";
                // $already_exist_value=$this->db->query("SELECT * FROM $table WHERE deleted=0 AND already_exist>0 AND employee_update_id=$request_id ORDER BY already_exist");
                $this->db->where('deleted', 0);
                $this->db->where('already_exist >', 0);
                $this->db->where('employee_update_id', $request_id);
                $this->db->order_by('already_exist');
                $already_exist_value = $this->db->get('employee_update_family');
                
                if($already_exist_value->num_rows() ==0)
                { } else {
                    $empid=$updated_family_val[0]['employee_id'];
                    $table1=$this->db->dbprefix."employee_family";
                    $old_fam=$this->db->query("SELECT * FROM $table1 WHERE deleted=0 AND employee_id=$empid ORDER BY name, birth_date");

                    //changes for bu
                    //$to_be_inserted_2=$this->db->dbprefix."user";
                    $insert_here=$this->db->dbprefix."employee_family_bu";
                    $this->db->query("INSERT INTO $insert_here(employee_id, employee_update_id , name , relationship , birth_date , occupation , employer , educational_attainment, degree, ecf_dependent , bir_dependent, hospitalization_dependent) SELECT $empid , $request_id , $table1.name , $table1.relationship , $table1.birth_date , $table1.occupation , $table1.employer, $table1.educational_attainment, $table1.degree, $table1.ecf_dependent, $table1.bir_dependents, $table1.hospitalization_dependents FROM $table1 WHERE $table1.employee_id=$empid");
                    //changes for bu

                    for($x=0;$x<$old_fam->num_rows();$x++)
                    {
                        $val=$old_fam->row_array($x);
                        $whattobesave=$already_exist_value->row_array($x);
                        $this->db->set('employee_id',$whattobesave['employee_id']);
                        $this->db->set('name',$whattobesave['name']);
                        $this->db->set('relationship',$whattobesave['relationship']);
                        $this->db->set('birth_date',$whattobesave['birthdate']);
                        $this->db->set('occupation',$whattobesave['occupation']);
                        $this->db->set('employer',$whattobesave['employer']);

                        $this->db->set('educational_attainment',$whattobesave['educational_attainment']);
                        $this->db->set('degree',$whattobesave['degree']);
                        $this->db->set('ecf_dependent',$whattobesave['ecf_dependent']);
                        $this->db->set('bir_dependents',$whattobesave['bir_dependent']);
                        $this->db->set('hospitalization_dependents',$whattobesave['hospitalization_dependent']);

                        $this->db->where('name',$val['name']);
                        $this->db->where('birth_date',$val['birth_date']);
                        $this->db->update('employee_family');
                    }
                }
                // $table2=$this->db->dbprefix."employee_update_family";
                // $already_exist_value=$this->db->query("SELECT * FROM $table2 WHERE deleted=0 AND already_exist=0 AND employee_update_id=$this->key_field_val ");
                $this->db->where('deleted', 0);
                $this->db->where('already_exist', 0);
                $this->db->where('employee_update_id', $request_id);
                // $this->db->order_by('already_exist');
                $already_exist_value = $this->db->get('employee_update_family');
                if($already_exist_value->num_rows() != 0)
                { 
                    $this->db->where('employee_update_id', $request_id);
                    $this->db->where('deleted', 0);
                    $this->db->where('already_exist', 0);
                    $updated_family = $this->db->get('employee_update_family');
                    foreach($updated_family->result_array() as $updated_val){
                         //$this->db->set('record_id', $request_id );
                         $this->db->set('employee_id', $updated_val['employee_id']);
                         $this->db->set('name', $updated_val['name']);
                         $this->db->set('relationship', $updated_val['relationship']);
                         $this->db->set('birth_date', $updated_val['birthdate']);
                         $this->db->set('occupation', $updated_val['occupation']);
                         $this->db->set('employer', $updated_val['employer']);

                         $this->db->set('educational_attainment',$updated_val['educational_attainment']);
                         $this->db->set('degree',$updated_val['degree']);
                         $this->db->set('ecf_dependent',$updated_val['ecf_dependent']);
                         $this->db->set('bir_dependents',$updated_val['bir_dependent']);
                         $this->db->set('hospitalization_dependents',$updated_val['hospitalization_dependent']);

                         $this->db->insert('employee_family');
                    }
                }
            }

            //updates

            //CHANGES FOR PERSONAL || This will only change date_of_marriage
            $this->db->where('employee_update_id', $request_id);
            $this->db->where('deleted', 0);
            $updated_personal = $this->db->get('employee_update');
            if($updated_personal->num_rows()!==0)
            {
                $updated_personal_val = $updated_personal->result_array();
                $empid=$updated_personal_val[0]['employee_id'];


                $this->db->set('civil_status_id',$updated_personal_val[0]['civil_status_id']);
                $this->db->where('employee_id', $empid);
                $this->db->where('deleted', 0);
                $this->db->update('employee');
            }


            //CHANGES FOR PERSONAL || This will only change date_of_marriage

            //changes for dateapproved
            $date_approved=date('Y-m-d H:i:s');
            $this->db->set('date_approved', $date_approved);
            $this->db->where('employee_update_id', $request_id);
            $this->db->update('Employee_update');
            //changes for dateapproved

            //to reset user access, delete user access file
            $app_directories =  $this->hdicore->_get_config('app_directories');
            if( file_exists( $app_directories['user_settings_dir'] . $request->row()->employee_id.'.php' ) ) unlink( $app_directories['user_settings_dir'] . $request->row()->employee_id.'.php');
            


            return true;
        }
        
    }

}