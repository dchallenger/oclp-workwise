<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Manpower_settings extends MY_Controller {
    function __construct() {
        parent::__construct();

        //set module variable values
        $this->grid_grouping = "";
        $this->related_table = array(); //table => field format

        $this->listview_title = 'Manpower Request';
        $this->listview_description = 'Lists all manpower requests';
        $this->jqgrid_title = "Manpower Request";
        $this->detailview_title = 'Manpower Request';
        $this->detailview_description = 'This page shows detailed information about a Manpower Request';
        $this->editview_title = 'Manpower Request Add/Edit';
        $this->editview_description = 'This page allows saving/editing information about a Manpower Request';       
    }

    // START - default module functions
    // default jqgrid controller method
    // function index() {
    //     $result = $this->db->get('recruitment_manpower_settings');
        
    //     if ($result && $result->num_rows() > 0) {            
    //         $settings = $result->row_array();
    //         redirect(base_url() . $this->module_link . '/edit/' . $settings['id']);
    //     } else {
    //         redirect(base_url() . $this->module_link . '/edit/-1');
    //     }
    // }
    function index()
    {
        $data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
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


    function detail() {
        parent::detail();

        //additional module detail routine here
        $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';        
        
        $data['content'] = 'detailview';                        

        //other views to load
        $data['views'] = array();

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
            parent::edit();
            
            $data['show_wizard_control'] = false;
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';
            if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
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

        //additional module delete routine here
    }

    // END - default module functions
    // START custom module funtions

    function get_settings() {
        if (IS_AJAX) {
            $qry = "SELECT a.*
						FROM {$this->db->dbprefix}user a 
						LEFT JOIN {$this->db->dbprefix}role b ON b.role_id = a.role_id
						LEFT JOIN {$this->db->dbprefix}role_profile c ON b.role_id = c.role_id
						LEFT JOIN {$this->db->dbprefix}user_position d ON d.position_id = a.position_id
						LEFT JOIN {$this->db->dbprefix}user_position_level e ON d.position_level_id = e.position_level_id
						WHERE c.profile_id IN (2,3,5)";
						
			$result = $this->db->query( $qry );
            $admins = $result->result_array();
            
            $response['notify']['admins'] = $admins;
            
            $settings = $this->db->get('recruitment_manpower_settings');
            if ($settings->num_rows() > 0) {
                $settings = $settings->row_array();
                $response['notify']['value'] = $settings['notify'];
                $response['email_to']['value'] = $settings['email_to'];
                $response['cc_to']['value'] = $settings['cc_to'];
            }
            
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }                
    }
}

/* End of file */
/* Location: system/application */