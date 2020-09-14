<?php

require_once APPPATH . 'controllers/admin/user_position.php';

class Positions extends User_Position {

    public function __construct() {
        parent::__construct();
        $this->load->helper('candidates');
        $this->listview_image = 'icons/vcard.png';
        $this->listview_title = 'Positions';
        $this->module_link = 'admin/user_position';
    }

    function _set_module_detail($class_path = '') {
        parent::_set_module_detail('admin/user_position');
    }

    function index() {
        $data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js	
        $data['content'] = 'recruitment/manpower/candidates/listview';        

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

}