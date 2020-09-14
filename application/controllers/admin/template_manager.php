<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Template_manager extends MY_Controller {

    function __construct() {
        parent::__construct();

        //set module variable values
        $this->grid_grouping = "";
        $this->related_table = array(); //table => field format	

        $this->listview_title = 'Template Manager';
        $this->listview_description = 'This module lists all defined Templates used mainly in emails and printing.';
        $this->jqgrid_title = "Templates List";
        $this->detailview_title = 'Template Info';
        $this->detailview_description = 'This page shows detailed information about a particular Template';
        $this->editview_title = 'Template Add/Edit';
        $this->editview_description = 'This page allows saving/editing information about Template';
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
        parent::detail();

        //additional module detail routine here
        $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';
        $data['content'] = 'detailview';

        //other views to load
        $data['views'] = array();

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

        //additional module save routine here
    }

    function delete() {
        parent::delete();

        //additional module delete routine here
    }
            
    // START custom module funtions
    
    function print_record($record_id = 0) {
        // Get from $_POST when the URI is not present.
        if ($record_id  == 0) {
            $record_id = $this->input->post('record_id');
        }

        $this->load->library('pdf');
        $this->load->model(array('uitype_detail', 'template'));

        $template = $this->template->get_template($record_id);

        // Get applicant details. (This returns the fieldgroup array.
        $check_record = $this->_record_exist($record_id);
        if ($check_record->exist) {
            $vars = array();
            
            $this->db->where($this->key_field, $record_id);                        
            $result = $this->db->get($this->module_table);

            if (!$result || $result->num_rows() == 0) {
                exit();
            }

            $vars = $result->row_array();
            
            // Replace $upload_url first to avoid TCPDF ERROR: [Image] Unable to get image: b[span%20class=.
            preg_match_all('/{\$upload_url}/',$template['body'], $matches);
            
            foreach ($matches[0] as $match) {
                $replace = base_url() . 'uploads/';
                $template['body'] = str_replace($match, $replace, $template['body']);
            }
            
            // Match all variables.
            preg_match_all('/{\$[a-zA-Z0-9_]*}/',$template['body'], $matches);
            
            foreach ($matches[0] as $match) {                                  
                $replace = '<b>[<span class="red">' . trim($match, '{$}') . '</span>]</b>';                
                $template['body'] = str_replace($match, $replace, $template['body']);
            }

            $template['body'] = '<style type="text/css">.red{color:red}</style>' . $template['body'];
            
            // Suppress errors because the template model does not take into account the possibility that a variable may not have been set.
            $html = $this->template->prep_message($template['body'], array(), false);

            // Prepare and output the PDF.
            $this->pdf->addPage();

            $this->pdf->writeHTML($html, true, false, false, false, '');
            $this->pdf->Output(date('Y-m-d').' '.$vars['templatename'] . '.PDF', 'D');
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }    
    
    function _default_grid_actions($module_link = "", $container = "") {
        // set default
        if ($module_link == "")
            $module_link = $this->module_link;
        if ($container == "")
            $container = "jqgridcontainer";

        $actions = '
            <span class="icon-group">
                <a class="icon-button icon-16-info" module_link="' . $module_link . '" tooltip="View" href="javascript:void(0)"></a>
                <a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>
                <a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>
                <a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>
            </span>';

        return $actions;
    }        
    
    // END custom module funtions
}

/* End of file */
/* Location: system/application */
?>