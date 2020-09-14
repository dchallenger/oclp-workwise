<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class resignation_demographics extends MY_Controller
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
        $this->load->plugin('ofc2');
        $this->_genderData = $this->_ageData = $this->_positionData = $this->_tenureData = array();
    }

    // START - default module functions
    // default jqgrid controller method
    function index()
    {
        $data['scripts'][] = multiselect_script();
        $data['scripts'][] = '<script type="text/javascript" src="'.base_url().'lib/ofc2/js/swfobject.js"></script>';
        $data['content'] = 'employee/resignation_demographics/index_view';

        if($this->session->flashdata('flashdata')){
            $info['flashdata'] = $this->session->flashdata('flashdata');
            $data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
        }

        $data['department'] = $this->db->get('user_company_department')->result_array();
        $data['company'] = $this->db->get('user_company')->result_array();
        $data['division'] = $this->db->get('user_company_division')->result_array();

        $data['positionDataTable']  = $this->positionDataQuery(0, 0, 0, 0, 0);

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
    // END default module funtions
    // START - custom module functions
    function get_division()
    {
        $division = $this->db->query('SELECT b.division_id, b.division FROM '.$this->db->dbprefix('user').' a LEFT JOIN  '.$this->db->dbprefix('user_company_division').' b ON a.division_id = b.division_id WHERE a.company_id IN ('.$this->input->post("div_id_delimited").') AND b.division_id IS NOT NULL GROUP BY b.division_id')->result_array();
        $html .= '<select id="division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
            foreach($division as $division_record){
                $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
            }
        $html .= '</select>';   

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);         
    }

    function get_department()
    {
        $department = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company_department').' WHERE '.$this->db->dbprefix('user_company_department').'.division_id IN ('.$this->input->post("div_id_delimited").')')->result_array();
        $html .= '<select id="department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
            foreach($department as $department_record){
                $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
            }
        $html .= '</select>';   

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);         
    }

    function generateAll()
    {
        $company_id = 0; 
        if(isset($_POST['company']))
        {
            $company_arr = array();
            foreach ($_POST['company'] as $value) 
            {
                $company_arr[] = $value;    
            }
          $company_id = implode(',', $company_arr);
        }
        $division_id = 0; 
        if(isset($_POST['division']))
        {
            $division_arr = array();
            foreach ($_POST['division'] as $value) 
            {
                $division_arr[] = $value;    
            }
          $division_id = implode(',', $division_arr);
        }
        $department_id = 0; 
        if(isset($_POST['department']))
        {
            $department_arr = array();
            foreach ($_POST['department'] as $value) 
            {
                $department_arr[] = $value;    
            }
          $department_id = implode(',', $department_arr);
        }        
        $date_from = 0;
        if(!empty($_POST['date_period_start']))
        {
            $date_from = $_POST['date_period_start'];
        }
        $date_to = 0;
        if(!empty($_POST['date_period_end']))
        {
            $date_to = $_POST['date_period_end'];
        }

       
        $json['positionData']       = $this->positionData($company_id,$division_id,$department_id,$date_from,$date_to,1);
        $position_stat              = $this->positionDataQuery($company_id, $division_id, $department_id, $date_from, $date_to);
        $emp_count2 = 0;
        foreach($position_stat as $k=>$v)
        {
            $emp_count2 += $v->resigned_count;
        }
        $json['emp_count2']          = $emp_count2;
        $json['position_statistics']  = $position_stat;

        $data['json']       = $json;
        $this->load->view('template/ajax', $data);
    }

    function positionData($company_id=0, $division_id=0, $department_id=0, $date_from=0, $date_to=0, $returnObj=false)
    {
        $title = new title( "Resigned Employees" );
        
        if( empty($this->_positionData) )
        {
            $this->_positionData = $this->positionDataQuery($company_id, $division_id, $department_id, $date_from, $date_to);
        }

        $data_1 = array();
        $data_2 = array();
        foreach($this->_positionData as $k=>$v)
        {
            $data_1[] = (FLoat)$v->resigned_count;
            $data_2[] = $v->resigned_date;
            $max_scale[] = $v->resigned_count;
        }

        $bar = new bar();
        $bar->set_values( $data_1 );
        $bar->set_colour( '#FFFF66' );
        $bar->set_tooltip( '#val#' );
        
        $max_scale = max($max_scale); 
        $step_scale = (int) ($max_scale * .25);

        $max_scale += ($max_scale * .25);
        $max_scale = (int) $max_scale;
        $y = new y_axis();
        // if($max_scale == 0)
        // {
            $y->set_range( 0, 50, 5 );
        // }
        // else
        // {
        //     $y->set_range( 0, $max_scale, $step_scale );
        // }
        $x = new x_axis();
        $x->set_labels_from_array( $data_2 );

        $chart = new open_flash_chart();
        $chart->set_title( $title );
        $chart->add_element( $bar );
        $chart->set_bg_colour( '#FFFFFF' );
        $chart->set_x_axis( $x );
        $chart->add_y_axis( $y );
        
        if($returnObj)
        {
            return $chart;
        }
        else
        {
            echo $chart->toPrettyString();
        }
    }

    function positionData2($company_id=0, $division_id=0, $department_id=0, $date_from=0, $date_to=0, $returnObj=false)
    {
        $title = new title( "Employee Type" );
        
        if( empty($this->_positionData) )
        {
            $this->_positionData = $this->positionDataQuery($company_id, $division_id, $department_id, $date_from, $date_to);
        }
        
        $bar_stack = new bar_stack();

        // set a cycle of 3 colours:
        $colors      = array('probationary_count' => '#FF3333', 'regular_count'=> '#006633' );
        $bar_caption = array('probationary_count' => 'Probationary', 'regular_count'=> 'Regular' );           
        $bar_stack->set_colours( array_values($colors) );        

        // bars from the x population         
        foreach($this->_positionData as $k=>$v)
        {
            $bar_stack->append_stack( array_values($v) );
             
            $max_scale[] = array_sum(array_values($v));
            $temp_keys = array_keys($v);
        }

        foreach($temp_keys as $k2=>$v2)
        {
             $bar_stack_keys[] =  new bar_stack_key($colors[$v2], $bar_caption[$v2]  , 13 );     
        }         
         
        $bar_stack->set_keys($bar_stack_keys);
        $bar_stack->set_tooltip('#x_label#, [#val#] <br>Total [#total#]' );
         
        $max_scale = max($max_scale); 
        $step_scale = (int) ($max_scale * .25);
        $max_scale += ($max_scale * .25);
        $max_scale = (int) $max_scale;

        $y = new y_axis();
        $y->set_range( 0, $max_scale, $step_scale );
          
        $x = new x_axis();
        $x->set_labels_from_array( array_keys($this->_positionData ) );

        $tooltip = new tooltip();
        $tooltip->set_hover();

        $chart = new open_flash_chart();
        $chart->set_title( $title );
        $chart->add_element( $bar_stack );
        $chart->set_x_axis( $x );
        $chart->add_y_axis( $y );           
           
        
        if($returnObj)
        {
            return $chart;
        }
        else
        {
            echo $chart->toPrettyString();
        }
    }

    //QUERY
    function positionDataQuery($company_id, $division_id, $department_id, $date_from, $date_to)
    {
        $company_sql = '';
        if($company_id != 0)
        {
            $company_sql = " AND ".$this->db->dbprefix('user').".company_id IN ({$company_id})";
        }   
        $division_sql = '';
        if($division_id != 0)
        {
            $division_sql = " AND ".$this->db->dbprefix('user').".division_id IN ({$division_id})";
        }
        $department_sql = '';
        if($department_id != 0)
        {
            $department_sql = " AND ".$this->db->dbprefix('user').".department_id IN ({$department_id})";
        }
        if($date_from == 0)
        {
            $date_from = date('Y');
        }
        if($date_to == 0)
        {
            $date_to = date('Y');
        }
        // $sql = "SELECT
        //           YEAR(a.resigned_date) as resigned_date,
        //           COUNT(YEAR(a.resigned_date)) as resigned_count
        //         FROM ".$this->db->dbprefix('employee')." a
        //           LEFT JOIN ".$this->db->dbprefix('user')." b
        //             ON a.user_id = b.user_id
        //         WHERE YEAR(a.resigned_date) >= '{$date_from}'
        //             AND YEAR(a.resigned_date) <= '{$date_to}'
        //             {$company_sql}{$division_sql}{$department_sql}
        //             GROUP BY YEAR(a.resigned_date)";
        $sql = "SELECT
                  YEAR(".$this->db->dbprefix('employee').".resigned_date) as resigned_date,
                  COUNT(YEAR(".$this->db->dbprefix('employee').".resigned_date)) as resigned_count
                FROM ".$this->db->dbprefix('employee_movement')."
                  LEFT JOIN ".$this->db->dbprefix('reason_for_leaving')."
                    ON ".$this->db->dbprefix('reason_for_leaving').".reason_for_leaving_id = ".$this->db->dbprefix('employee_movement').".reason_for_leaving
                  LEFT JOIN ".$this->db->dbprefix('user')."
                    ON ".$this->db->dbprefix('employee_movement').".employee_id = ".$this->db->dbprefix('user').".employee_id
                  LEFT JOIN ".$this->db->dbprefix('employee')."
                    ON ".$this->db->dbprefix('employee').".employee_id = ".$this->db->dbprefix('user').".employee_id
                  LEFT JOIN ".$this->db->dbprefix('user_job_title')."
                    ON ".$this->db->dbprefix('employee').".job_title = ".$this->db->dbprefix('user_job_title').".job_title_id
                  LEFT JOIN ".$this->db->dbprefix('user_position')."
                    ON ".$this->db->dbprefix('user').".position_id = ".$this->db->dbprefix('user_position').".position_id
                WHERE YEAR(".$this->db->dbprefix('employee').".resigned_date) >= '{$date_from}'
                    AND YEAR(".$this->db->dbprefix('employee').".resigned_date) <= '{$date_to}'                    
                    AND ".$this->db->dbprefix('employee_movement').".employee_movement_type_id = 6
                    AND ".$this->db->dbprefix('employee_movement').".status = 6
                    {$company_sql}{$division_sql}{$department_sql}
                    GROUP BY YEAR(".$this->db->dbprefix('employee').".resigned_date)";
        $qry = $this->db->query($sql);
        if( $qry->num_rows()>0 )
        {
            $r = $qry->result();
            foreach( (array)$r as $k => $o)
            {
                $row[$o->resigned_date] = $o;
            }
        }
        while($date_from <= $date_to)
        {   
            $z=(object)null;
            if(!array_key_exists($date_from, $row))
            {
                $z->resigned_date=$date_from;
                $z->resigned_count=0;
                $row[$date_from] = $z;
            }
            $date_from++;
        }
        ksort($row);
        $qry->free_result();
        return $row;
    }
    // END custom module funtions
}

/* End of file */
/* Location: system/application */