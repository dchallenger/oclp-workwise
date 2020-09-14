<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_demographics extends MY_Controller
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
		$data['content'] = 'employee/demographics/index_view';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		$data['department'] = $this->db->get('user_company_department')->result_array();
		$data['company'] = $this->db->get('user_company')->result_array();
		$data['division'] = $this->db->get('user_company_division')->result_array();

        $data['genderDataTable']    = $this->genderDataQuery(0, 0, 0, 0, 0);
        $data['ageDataTable']       = $this->ageDataQuery(0, 0, 0, 0, 0);
        $data['positionDataTable']  = $this->positionDataQuery(0, 0, 0, 0, 0);
        $data['tenureDataTable']    = $this->tenureDataQuery(0, 0, 0, 0, 0);

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
            $date_from = date('Y-m-d',strtotime($_POST['date_period_start']));
        }
        $date_to = 0;
        if(!empty($_POST['date_period_end']))
        {
            $date_to = date('Y-m-d',strtotime($_POST['date_period_end']));
        }


        $json['genderData']         = $this->genderData($company_id,$division_id,$department_id,$date_from,$date_to,1);        
        $gender_stat                = $this->genderDataQuery($company_id, $division_id, $department_id, $date_from, $date_to);
        $emp_count = 0;
        foreach($gender_stat as $k=>$v)
        {
            $emp_count += array_sum(array_values($v));
        }
        $json['emp_count']          = $emp_count;
        $json['gender_statistics']  = $gender_stat;

        $json['ageData']         = $this->ageData($company_id,$division_id,$department_id,$date_from,$date_to,1);
        $age_stat                = $this->ageDataQuery($company_id, $division_id, $department_id, $date_from, $date_to);
        foreach( (array)$age_stat as $line => $o){
            $json['age_labels'][$o->short_name] = number_format($o->group_count*1/$o->employee_count*100, 2,'.','').'% <b>('.($o->group_count*1).')</b> ';
        }

        $json['positionData']       = $this->positionData($company_id,$division_id,$department_id,$date_from,$date_to,1);
        $position_stat              = $this->positionDataQuery($company_id, $division_id, $department_id, $date_from, $date_to);
        $emp_count2 = 0;
        foreach($position_stat as $k=>$v)
        {
            $emp_count2 += array_sum(array_values($v));
        }
        $json['emp_count2']          = $emp_count2;
        $json['position_statistics']  = $position_stat;
        $json['position_labels'] = array(0=>'RANK & FILE');

        $json['tenureData']         = $this->tenureData($company_id,$division_id,$department_id,$date_from,$date_to,1);
        $tenure_stat                = $this->tenureDataQuery($company_id, $division_id, $department_id, $date_from, $date_to);
        $employee_count = 0;
        foreach( (array)$tenure_stat as $id => $o){
        $employee_count += $o->group_count;
        }
        foreach( (array)$tenure_stat as $id => $o){
            $json['tenure_labels'][str_replace(' ','_', $o->title)] = number_format($o->group_count*1/$employee_count*100, 2,'.','').'% <b>('.($o->group_count*1).')</b> ';
        }

        $data['json']       = $json;
        $this->load->view('template/ajax', $data);
	}

    function genderData($company_id=0, $division_id=0, $department_id=0, $date_from=0, $date_to=0, $returnObj=false)
    {
        $title = new title( "Gender" );

        if(empty($this->_genderData))
        {            
            $this->_genderData = $this->genderDataQuery($company_id, $division_id, $department_id, $date_from, $date_to);
        }
        $data_1 = array();        
        $data_2 = array();
        $data_3 = array();
        $data_4 = array();
        $data_5 = array();
        $data_6 = array();
        $data_7 = array();
        foreach($this->_genderData as $k=>$v)
        {
            $data_1[] = $v['regular_count'];
            $data_2[] = $v['probationary_count'];
            $data_3[] = $v['consultant_count'];
            $data_4[] = $v['project_employee_count'];
            $data_5[] = $v['contractual_direct_count'];
            $data_6[] = $v['contractual_agent_count'];
            $data_7[] = $v['ojt_count'];
            $max_scale[] = array_sum(array_values($v));
        }

        $bar = new bar();
        $bar->set_values( $data_1 );
        $bar->set_colour( '#FFFF66' );
        $bar->set_tooltip( 'Regular<br>Value:#val#' );
        $bar_2 = new bar();
        $bar_2->set_values( $data_2 ); 
        $bar_2->set_colour( '#FF9966' );       
        $bar_2->set_tooltip( 'Probationary<br>Value:#val#' );
        $bar_3 = new bar();
        $bar_3->set_values( $data_3 );
        $bar_3->set_colour( '#FF3366' );
        $bar_3->set_tooltip( 'Consultant<br>Value:#val#' );
        $bar_4 = new bar();
        $bar_4->set_values( $data_4 );
        $bar_4->set_colour( '#CCFF66' );
        $bar_4->set_tooltip( 'Project Employee<br>Value:#val#' );
        $bar_5 = new bar();
        $bar_5->set_values( $data_5 );
        $bar_5->set_colour( '#CC9966' );
        $bar_5->set_tooltip( 'Contractual (Direct Hired)<br>Value:#val#' );
        $bar_6 = new bar();
        $bar_6->set_values( $data_6 );
        $bar_6->set_colour( '#99FF66' );
        $bar_6->set_tooltip( 'Contractual (Agency Hired)<br>Value:#val#' );
        $bar_7 = new bar();
        $bar_7->set_values( $data_7 );
        $bar_7->set_colour( '#999966' );
        $bar_7->set_tooltip( 'On-the-Job Training<br>Value:#val#' );
        
        $max_scale = max($max_scale); 
        $step_scale = (int) ($max_scale * .25);

        $max_scale += ($max_scale * .25);
        $max_scale = (int) $max_scale;
        $y = new y_axis();
        $y->set_range( 0, $max_scale, $step_scale );
        $x = new x_axis();
        $x->set_labels_from_array( array_keys($this->_genderData ) );

        $chart = new open_flash_chart();
        $chart->set_title( $title );
        $chart->add_element( $bar );
        $chart->add_element( $bar_2 );
        $chart->add_element( $bar_3 );
        $chart->add_element( $bar_4 );
        $chart->add_element( $bar_5 );
        $chart->add_element( $bar_6 );
        $chart->add_element( $bar_7 );
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

	function genderData2($company_id=0, $division_id=0, $department_id=0, $date_from=0, $date_to=0, $returnObj=false)
	{
		$title = new title( "Gender" );

        $bar_stack = new bar_stack();

        // set a cycle of 3 colours:
        $colors      = array('probationary_count' => '#FF0000', 'regular_count'=> '#0000FF' );
        $bar_caption = array('probationary_count' => 'Probationary', 'regular_count'=> 'Regular' );           
        $bar_stack->set_colours( array_values($colors) );        

        // bars from the x population
        if(empty($this->_genderData))
        {            
            $this->_genderData = $this->genderDataQuery($company_id, $division_id, $department_id, $date_from, $date_to);
        }

        foreach($this->_genderData as $k=>$v)
        {
            $bar_stack->append_stack( array_values($v));
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
        $x->set_labels_from_array( array_keys($this->_genderData ) );

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

    function ageData($company_id=0, $division_id=0, $department_id=0, $date_from=0, $date_to=0, $returnObj=false)
    {
        $title = new title( 'Age Profile' );
        
        if( empty($this->_ageData) )
        {
            $this->_ageData = $this->ageDataQuery($company_id, $division_id, $department_id, $date_from, $date_to);
        }
        
        //dbug($this->_ageData);
        foreach( (array)$this->_ageData as $line => $o){
            $data[] = new pie_value($o->group_count*1, $o->title);
        }
            
        $pie = new pie();
        $pie->alpha(0.8)
            ->add_animation( new pie_fade() )
            ->add_animation( new pie_bounce(5) )
            //->start_angle( 270 )
            ->start_angle( 0 )
            ->tooltip( '#percent#' )
            ->colours(array("#b5121b","#C4D318","#0077CC","#ce59c4","#ffa800"));
        
        $pie->set_values( $data );
        
        $chart = new open_flash_chart();
        $chart->set_title( $title );
        $chart->add_element( $pie );

        if($returnObj)
        {
            return $chart;
        }
        else
        {
            echo $chart->toPrettyString();
        }
    }

    function positionData($company_id=0, $division_id=0, $department_id=0, $date_from=0, $date_to=0, $returnObj=false)
    {
        $title = new title( "Employee Type" );
        
        if( empty($this->_positionData) )
        {
            $this->_positionData = $this->positionDataQuery($company_id, $division_id, $department_id, $date_from, $date_to);
        }
        
        $data_1 = array();        
        $data_2 = array();
        $data_3 = array();
        $data_4 = array();
        $data_5 = array();
        $data_6 = array();
        $data_7 = array();
        foreach($this->_positionData as $k=>$v)
        {
            $data_1[] = $v['regular_count'];
            $data_2[] = $v['probationary_count'];
            $data_3[] = $v['consultant_count'];
            $data_4[] = $v['project_employee_count'];
            $data_5[] = $v['contractual_direct_count'];
            $data_6[] = $v['contractual_agent_count'];
            $data_7[] = $v['ojt_count'];
            $max_scale[] = array_sum(array_values($v));
        }

        $bar = new bar();
        $bar->set_values( $data_1 );
        $bar->set_colour( '#FFFF66' );
        $bar->set_tooltip( 'Regular<br>Value:#val#' );
        $bar_2 = new bar();
        $bar_2->set_values( $data_2 ); 
        $bar_2->set_colour( '#FF9966' );       
        $bar_2->set_tooltip( 'Probationary<br>Value:#val#' );
        $bar_3 = new bar();
        $bar_3->set_values( $data_3 );
        $bar_3->set_colour( '#FF3366' );
        $bar_3->set_tooltip( 'Consultant<br>Value:#val#' );
        $bar_4 = new bar();
        $bar_4->set_values( $data_4 );
        $bar_4->set_colour( '#CCFF66' );
        $bar_4->set_tooltip( 'Project Employee<br>Value:#val#' );
        $bar_5 = new bar();
        $bar_5->set_values( $data_5 );
        $bar_5->set_colour( '#CC9966' );
        $bar_5->set_tooltip( 'Contractual (Direct Hired)<br>Value:#val#' );
        $bar_6 = new bar();
        $bar_6->set_values( $data_6 );
        $bar_6->set_colour( '#99FF66' );
        $bar_6->set_tooltip( 'Contractual (Agency Hired)<br>Value:#val#' );
        $bar_7 = new bar();
        $bar_7->set_values( $data_7 );
        $bar_7->set_colour( '#999966' );
        $bar_7->set_tooltip( 'On-the-Job Training<br>Value:#val#' );
        
        $max_scale = max($max_scale); 
        $step_scale = (int) ($max_scale * .25);

        $max_scale += ($max_scale * .25);
        $max_scale = (int) $max_scale;
        $y = new y_axis();
        $y->set_range( 0, $max_scale, $step_scale );
        $x = new x_axis();
        $x->set_labels_from_array( array_keys($this->_positionData ) );

        $chart = new open_flash_chart();
        $chart->set_title( $title );
        $chart->add_element( $bar );
        $chart->add_element( $bar_2 );
        $chart->add_element( $bar_3 );
        $chart->add_element( $bar_4 );
        $chart->add_element( $bar_5 );
        $chart->add_element( $bar_6 );
        $chart->add_element( $bar_7 );
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

    function tenureData($company_id=0, $division_id=0, $department_id=0, $date_from=0, $date_to=0, $returnObj=false)
    {
        $title = new title( 'Tenure (Work Force)' );
        
        if( empty($this->_tenureData) )
        {
            $this->_tenureData = $this->tenureDataQuery($company_id, $division_id, $department_id, $date_from, $date_to);
        }

        foreach( (array)$this->_tenureData as $idx => $o)
        {
            $data2[] = new pie_value($o->group_count*1, $o->title);
        }
            
        $pie = new pie();
        $pie->alpha(0.8)
            ->add_animation( new pie_fade() )
            ->add_animation( new pie_bounce(5) )
            //->start_angle( 270 )
            ->start_angle( 0 )
            ->tooltip( '#percent#' )
            ->colours(array("#b5121b","#C4D318","#0077CC","#ce59c4","#ffa800"));
        
        $pie->set_values( $data2 );
        
        $chart = new open_flash_chart();
        $chart->set_title( $title );
        $chart->add_element( $pie );

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
    function genderDataQuery($company_id, $division_id, $department_id, $date_from, $date_to)
    {
        $company_sql = '';
        if($company_id != 0)
        {
            $company_sql = " AND b.company_id IN ({$company_id})";
        }   
        $division_sql = '';
        if($division_id != 0)
        {
            $division_sql = " AND b.division_id IN ({$division_id})";
        }
        $department_sql = '';
        if($department_id != 0)
        {
            $department_sql = " AND b.department_id IN ({$department_id})";
        }
        if($date_from == 0)
        {
            $date_from = date('Y-m-d');
        }
        if($date_to == 0)
        {
            $date_to = date('Y-m-d');
        }
        $sql = "SELECT
                    SUM(IF(a.employee_id <> 0 ,1,0)) AS total_count,
                    SUM(IF(a.status_id = 1,1,0)) AS regular_count,
                    SUM(IF(a.status_id = 2,1,0)) AS probationary_count,
                    SUM(IF(a.status_id = 3,1,0)) AS consultant_count,
                    SUM(IF(a.status_id = 4,1,0)) AS project_employee_count,
                    SUM(IF(a.status_id = 5,1,0)) AS contractual_direct_count,
                    SUM(IF(a.status_id = 6,1,0)) AS contractual_agent_count,
                    SUM(IF(a.status_id = 7,1,0)) AS ojt_count,
                    'Male' AS gender_tag
                FROM ".$this->db->dbprefix('employee')." a
                    LEFT JOIN ".$this->db->dbprefix('user')." b
                        ON a.user_id = b.user_id
                WHERE b.sex = 'male' 
                    AND (IF(a.employed_date IS NOT NULL, a.employed_date <= '{$date_from}' , 1)
                    AND IF(a.resigned_date IS NOT NULL,  a.resigned_date >= '{$date_to}' , 1))
                    {$company_sql}{$division_sql}{$department_sql}
                UNION ALL
                SELECT
                    SUM(IF(a.employee_id <> 0 ,1,0)) AS total_count,
                    SUM(IF(a.status_id = 1,1,0)) AS regular_count,
                    SUM(IF(a.status_id = 2,1,0)) AS probationary_count,
                    SUM(IF(a.status_id = 3,1,0)) AS consultant_count,
                    SUM(IF(a.status_id = 4,1,0)) AS project_employee_count,
                    SUM(IF(a.status_id = 5,1,0)) AS contractual_direct_count,
                    SUM(IF(a.status_id = 6,1,0)) AS contractual_agent_count,
                    SUM(IF(a.status_id = 7,1,0)) AS ojt_count,
                    'Female' AS gender_tag
                FROM ".$this->db->dbprefix('employee')." a
                    LEFT JOIN ".$this->db->dbprefix('user')." b
                        ON a.user_id = b.user_id
                WHERE b.sex = 'female' 
                    AND (IF(a.employed_date IS NOT NULL, a.employed_date <= '{$date_from}' , 1)
                    AND IF(a.resigned_date IS NOT NULL,  a.resigned_date >= '{$date_to}' , 1))
                    {$company_sql}{$division_sql}{$department_sql}";
                    // dbug($sql);
        $qry = $this->db->query($sql);
        foreach($qry->result() as $k=>$v)
        {
            $temp                           = array();
            $temp['regular_count']          = (int) $v->regular_count;
            $temp['probationary_count']     = (int) $v->probationary_count;
            $temp['consultant_count']           = (int) $v->consultant_count;
            $temp['project_employee_count']     = (int) $v->project_employee_count;
            $temp['contractual_direct_count']   = (int) $v->contractual_direct_count;
            $temp['contractual_agent_count']    = (int) $v->contractual_agent_count;
            $temp['ojt_count']                  = (int) $v->ojt_count;
            // asort($temp,SORT_NUMERIC );
            $row[$v->gender_tag]            = $temp;            
        }

        $qry->free_result();
        return $row;
    }

    function ageDataQuery($company_id, $division_id, $department_id, $date_from, $date_to)
    {
        $company_sql = '';
        if($company_id != 0)
        {
            $company_sql = " AND b.company_id IN ({$company_id})";
        }   
        $division_sql = '';
        if($division_id != 0)
        {
            $division_sql = " AND b.division_id IN ({$division_id})";
        }
        $department_sql = '';
        if($department_id != 0)
        {
            $department_sql = " AND b.department_id IN ({$department_id})";
        }
        if($date_from == 0)
        {
            $date_from = date('Y-m-d');
        }
        if($date_to == 0)
        {
            $date_to = date('Y-m-d');
        }
        $date_now = date('Y-m-d');
        $sql = "SELECT
                  1               AS line,
                  'Below 25 y.o.' AS title,
                  'below_25'      AS short_name,
                  COUNT(a.employee_id)    employee_count,
                  SUM(IF(DATEDIFF('{$date_now}',b.birth_date)/365 < 25 OR ISNULL(b.birth_date), 1, 0))    group_count
                FROM ".$this->db->dbprefix('employee')." a
                  LEFT JOIN ".$this->db->dbprefix('user')." b
                    ON a.employee_id = b.employee_id
                WHERE (IF(a.employed_date IS NOT NULL,  a.employed_date <= '{$date_from}', 1)
                       AND IF(a.resigned_date IS NOT NULL,  a.resigned_date >= '{$date_to}', 1))
                       {$company_sql}{$division_sql}{$department_sql}
                UNION ALL 
                SELECT
                   2                AS line,
                   '25 to 35 y.o.'  AS title,
                   '25_to_35'       AS short_name,
                   COUNT(a.employee_id)    employee_count,
                   SUM(IF(DATEDIFF('{$date_now}',b.birth_date)/365 >= 25 AND DATEDIFF('{$date_now}',b.birth_date)/365 < 36, 1, 0))    group_count
                FROM ".$this->db->dbprefix('employee')." a
                   LEFT JOIN ".$this->db->dbprefix('user')." b
                     ON a.employee_id = b.employee_id
                WHERE (IF(a.employed_date IS NOT NULL,  a.employed_date <= '{$date_from}', 1)
                      AND IF(a.resigned_date IS NOT NULL,  a.resigned_date >= '{$date_to}', 1))
                    {$company_sql}{$division_sql}{$department_sql}
                UNION ALL 
                SELECT
                   3            AS line, 
                   '36 to 45 y.o.'  AS title, 
                   '36_to_45'       AS short_name, 
                   COUNT(a.employee_id)     employee_count,
                   SUM(IF(DATEDIFF('{$date_now}',b.birth_date)/365 >= 36 AND DATEDIFF('{$date_now}',b.birth_date)/365 < 46, 1, 0))group_count
                FROM ".$this->db->dbprefix('employee')." a
                   LEFT JOIN ".$this->db->dbprefix('user')." b
                     ON a.employee_id = b.employee_id
                WHERE (IF(a.employed_date IS NOT NULL,  a.employed_date <= '{$date_from}', 1)
                      AND IF(a.resigned_date IS NOT NULL,  a.resigned_date >= '{$date_to}', 1))
                    {$company_sql}{$division_sql}{$department_sql}
                UNION ALL 
                SELECT
                   4            AS line, 
                   '46 to 55 y.o.'  AS title, 
                   '46_to_55'       AS short_name, 
                   COUNT(a.employee_id)employee_count,
                   SUM(IF(DATEDIFF('{$date_now}',b.birth_date)/365 >= 46 AND DATEDIFF('{$date_now}',b.birth_date)/365 < 56, 1, 0))group_count
                FROM ".$this->db->dbprefix('employee')." a
                   LEFT JOIN ".$this->db->dbprefix('user')." b
                     ON a.employee_id = b.employee_id
                WHERE (IF(a.employed_date IS NOT NULL,  a.employed_date <= '{$date_from}', 1)
                      AND IF(a.resigned_date IS NOT NULL,  a.resigned_date >= '{$date_to}', 1))
                    {$company_sql}{$division_sql}{$department_sql}
                UNION ALL 
                SELECT
                   5            AS line, 
                   'above 55 y.o.'  AS title, 
                   'above_55'       AS short_name, 
                   COUNT(a.employee_id)employee_count,
                   SUM(IF(DATEDIFF('{$date_now}',b.birth_date)/365 >= 56, 1, 0))group_count
                FROM ".$this->db->dbprefix('employee')." a
                   LEFT JOIN ".$this->db->dbprefix('user')." b
                     ON a.employee_id = b.employee_id
                WHERE (IF(a.employed_date IS NOT NULL,  a.employed_date <= '{$date_from}', 1)
                       AND IF(a.resigned_date IS NOT NULL,  a.resigned_date >= '{$date_to}', 1))
                    {$company_sql}{$division_sql}{$department_sql}";
        $qry = $this->db->query($sql);
        if( $qry->num_rows()>0 ){
            $r = $qry->result();
            
            foreach( (array)$r as $k => $o){
                $row[$o->line] = $o;
            }
        }
        $qry->free_result();
        return $row;
    }

    function positionDataQuery($company_id, $division_id, $department_id, $date_from, $date_to)
    {
        $company_sql = '';
        if($company_id != 0)
        {
            $company_sql = " AND c.company_id IN ({$company_id})";
        }   
        $division_sql = '';
        if($division_id != 0)
        {
            $division_sql = " AND c.division_id IN ({$division_id})";
        }
        $department_sql = '';
        if($department_id != 0)
        {
            $department_sql = " AND c.department_id IN ({$department_id})";
        }
        if($date_from == 0)
        {
            $date_from = date('Y-m-d');
        }
        if($date_to == 0)
        {
            $date_to = date('Y-m-d');
        }
        $date_now = date('Y-m-d');
        $sql = "SELECT
                  b.employee_type_id as id,
                  b.employee_type as name,
                  COUNT(c.level_id) AS group_count,
                  SUM(c.is_regular_count) AS regular_count,
                  SUM(c.is_probationary_count) AS probationary_count,
                  SUM(c.is_consultant_count) AS consultant_count,
                  SUM(c.is_project_employee_count) AS project_employee_count,
                  SUM(c.is_contractual_direct_count) AS contractual_direct_count,
                  SUM(c.is_contractual_agent_count) AS contractual_agent_count,
                  SUM(c.is_ojt_count) AS ojt_count
                FROM ".$this->db->dbprefix('employee_type')." b
                  LEFT JOIN (SELECT
                               a.employee_type,
                               IF(a.employee_type != '',0,a.employee_type) AS level_id,
                               IF(a.status_id = 1,1,0) AS is_regular_count,
                               IF(a.status_id = 2,1,0) AS is_probationary_count,
                               IF(a.status_id = 3,1,0) AS is_consultant_count,
                               IF(a.status_id = 4,1,0) AS is_project_employee_count,
                               IF(a.status_id = 5,1,0) AS is_contractual_direct_count,
                               IF(a.status_id = 6,1,0) AS is_contractual_agent_count,
                               IF(a.status_id = 7,1,0) AS is_ojt_count
                             FROM ".$this->db->dbprefix('employee')." a
                              LEFT JOIN ".$this->db->dbprefix('user')." c
                                ON a.employee_id = c.employee_id
                              WHERE (IF(a.employed_date IS NOT NULL,  a.employed_date <= '{$date_from}', 1)
                                    AND IF(a.resigned_date IS NOT NULL,  a.resigned_date >= '{$date_to}', 1))
                                    {$company_sql}{$division_sql}{$department_sql}) c
                    ON b.employee_type_id = c.employee_type
                WHERE b.deleted = 0
                GROUP BY b.employee_type_id
                HAVING COUNT(c.level_id) > 0";
        // dbug($sql);
        $qry = $this->db->query($sql);
        foreach($qry->result() as $k=>$v)
        {
            $temp                           = array();
            $temp['regular_count']          = (int) $v->regular_count;
            $temp['probationary_count']     = (int) $v->probationary_count;
            $temp['consultant_count']           = (int) $v->consultant_count;
            $temp['project_employee_count']     = (int) $v->project_employee_count;
            $temp['contractual_direct_count']   = (int) $v->contractual_direct_count;
            $temp['contractual_agent_count']    = (int) $v->contractual_agent_count;
            $temp['ojt_count']                  = (int) $v->ojt_count;
            //asort($temp,SORT_NUMERIC );
            $row[$v->name] = $temp;            
        }

        $qry->free_result();
        return $row;
    }

    function tenureDataQuery($company_id, $division_id, $department_id, $date_from, $date_to)
    {
        $company_sql = '';
        if($company_id != 0)
        {
            $company_sql = " AND b.company_id IN ({$company_id})";
        }   
        $division_sql = '';
        if($division_id != 0)
        {
            $division_sql = " AND b.division_id IN ({$division_id})";
        }
        $department_sql = '';
        if($department_id != 0)
        {
            $department_sql = " AND b.department_id IN ({$department_id})";
        }
        if($date_from == 0)
        {
            $date_from = date('Y-m-d');
        }
        if($date_to == 0)
        {
            $date_to = date('Y-m-d');
        }
        $date_now = date('Y-m-d');
        $sql = "SELECT
                    1 AS line, 'Below 1 yr' AS title, 
                    SUM(IF(DATEDIFF('{$date_now}',a.employed_date)/365 < 1 OR ISNULL(a.employed_date), 1,0))group_count
                FROM ".$this->db->dbprefix('employee')." a
                    LEFT JOIN ".$this->db->dbprefix('user')." b
                        ON a.user_id = b.user_id
                WHERE (IF(a.employed_date IS NOT NULL,  a.employed_date <= '{$date_from}', 1)
                    AND IF(a.resigned_date IS NOT NULL,  a.resigned_date >= '{$date_to}', 1))
                    {$company_sql}{$division_sql}{$department_sql}
                UNION ALL
                SELECT
                    2 AS line, '1 - 5 yrs' AS title,  
                    SUM(IF(DATEDIFF('{$date_now}',a.employed_date)/365 >= 1 AND DATEDIFF('{$date_now}',a.employed_date)/365 < 6, 1,0))group_count
                FROM ".$this->db->dbprefix('employee')." a
                    LEFT JOIN ".$this->db->dbprefix('user')." b
                        ON a.user_id = b.user_id
                WHERE (IF(a.employed_date IS NOT NULL,  a.employed_date <= '{$date_from}', 1)
                    AND IF(a.resigned_date IS NOT NULL,  a.resigned_date >= '{$date_to}', 1))
                    {$company_sql}{$division_sql}{$department_sql}
                UNION ALL    
                SELECT
                    3 AS line, '6 - 10 yrs' AS title,  
                    SUM(IF(DATEDIFF('{$date_now}',a.employed_date)/365 >= 6 AND DATEDIFF('{$date_now}',a.employed_date)/365 < 11, 1,0))group_count
                FROM ".$this->db->dbprefix('employee')." a
                    LEFT JOIN ".$this->db->dbprefix('user')." b
                        ON a.user_id = b.user_id
                WHERE (IF(a.employed_date IS NOT NULL,  a.employed_date <= '{$date_from}', 1)
                    AND IF(a.resigned_date IS NOT NULL,  a.resigned_date >= '{$date_to}', 1))
                    {$company_sql}{$division_sql}{$department_sql}
                UNION ALL   
                SELECT
                    4 AS line, '11 - 20 yrs' AS title,  
                    SUM(IF(DATEDIFF('{$date_now}',a.employed_date)/365 >= 11 AND DATEDIFF('{$date_now}',a.employed_date)/365 < 21, 1,0))group_count
                FROM ".$this->db->dbprefix('employee')." a
                    LEFT JOIN ".$this->db->dbprefix('user')." b
                        ON a.user_id = b.user_id
                WHERE (IF(a.employed_date IS NOT NULL,  a.employed_date <= '{$date_from}', 1)
                    AND IF(a.resigned_date IS NOT NULL,  a.resigned_date >= '{$date_to}', 1))
                    {$company_sql}{$division_sql}{$department_sql}
                UNION ALL    
                SELECT
                    5 AS line, 'Above 20 yrs' AS title,  
                    SUM(IF(DATEDIFF('{$date_now}',a.employed_date)/365 >= 21, 1,0))group_count
                FROM ".$this->db->dbprefix('employee')." a
                    LEFT JOIN ".$this->db->dbprefix('user')." b
                        ON a.user_id = b.user_id
                WHERE (IF(a.employed_date IS NOT NULL,  a.employed_date <= '{$date_from}', 1)
                    AND IF(a.resigned_date IS NOT NULL,  a.resigned_date >= '{$date_to}', 1))
                    {$company_sql}{$division_sql}{$department_sql}";

        $qry = $this->db->query($sql);
        if( $qry->num_rows()>0 ){
            $r = $qry->result();
            
            foreach( (array)$r as $k => $o){
                $row[$o->line] = $o;
            }
        }

        $qry->free_result();
        return $row;
    }
	// END custom module funtions
}

/* End of file */
/* Location: system/application */