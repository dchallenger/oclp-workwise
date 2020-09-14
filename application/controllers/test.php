<?php

class Test extends CI_Controller {

        public function index() {
                $this->load->model('applicant/Applicant_skills', 'test');
                var_dump($this->test);
        }
}
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */