<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Roster2 extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = '';
		$this->listview_description = 'This module lists all defined (s).';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a particular ';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about ';	
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'employee/roster2/listview';
		$data['jqgrid'] = 'employee/roster2/jqgrid';

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

		$data['department'] = $this->db->get('user_company_department')->result_array();
		$data['company'] = $this->db->get('user_company')->result_array();
		$data['division'] = $this->db->get('user_company_division')->result_array();

		if (!$this->superadmin){
			$this->db->where('reporting_to', $this->userinfo['position_id']);
			$this->db->where('deleted', 0);
			$result	= $this->db->get('user_position');			
			if ($result){
				$subordinates = $result->num_rows();
			}
		}
		else{
			$subordinates = 1;
		}

		$data['w_subordinates'] = $subordinates;

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
		parent::ajax_save();
		
		//additional module save routine here
				
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}
	// END - default module functions
	
	// START custom module funtions

	function listview()
	{
		if ($this->input->post('_search') == 'false'){
            $response->rows[$ctr]['cell'][0] = "";
            $response->rows[$ctr]['cell'][1] = "";
            $response->rows[$ctr]['cell'][2] = "";
            $response->rows[$ctr]['cell'][3] = "";
            $response->rows[$ctr]['cell'][4] = "";
		}
		else{	
	        $page = $this->input->post('page');
	        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
	        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
	        $sord = $this->input->post('sord'); // get the direction        
			
			$search = 1;			

			$this->db->select(''.$this->db->dbprefix. 'user_company_department.department_id'.' as "DepartmentId",'.$this->db->dbprefix. 'user_company_division.division_id'.' as "DivisionId"');
			$this->db->select(''.$this->db->dbprefix. 'user_company_division.division'.' as "Division",'.$this->db->dbprefix. 'user_company_department.department'.' as "Department",'.$this->db->dbprefix. 'user_position.position'.' as "Position"');
			$this->db->select('CONCAT(' . $this->db->dbprefix . 'user.firstname," ", LEFT(' . $this->db->dbprefix . 'user.middlename,1)," . ", user.lastname) as "Full Name"', false);
			$this->db->select(''.$this->db->dbprefix. 'user_rank.job_rank'.' as "Rank Range",'.$this->db->dbprefix. 'user_rank_code.job_rank_code'.' as "Rank Code"');
			$this->db->from('user');
			$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee').'.employee_id');
			$this->db->join($this->db->dbprefix('user_company_division'),$this->db->dbprefix('user').'.division_id = '.$this->db->dbprefix('user_company_division').'.division_id',"left");
			$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
			$this->db->join($this->db->dbprefix('user_position'),$this->db->dbprefix('user').'.position_id = '.$this->db->dbprefix('user_position').'.position_id',"left");		
			$this->db->join($this->db->dbprefix('user_rank_code'),$this->db->dbprefix('employee').'.rank_code = '.$this->db->dbprefix('user_rank_code').'.job_rank_code_id',"left");		
			$this->db->join('user_rank','employee.rank_id = user_rank.job_rank_id','left');			
			$this->db->where("user.deleted = 0 AND {$this->db->dbprefix}employee.status_id < 3 AND ".$search);	

			$department = implode(",", $this->input->post('department'));

			if ($this->userinfo['login'] != "superadmin"){
				$this->db->where("{$this->db->dbprefix}user.department_id IN ({$department})");
			}
			else{
				if ($this->input->post('company') && $this->input->post('company') != 'null'){
					$this->db->where_in($this->db->dbprefix('user').'.company_id ',$this->input->post('company'));
				}				
				if ($this->input->post('division') && $this->input->post('division') != 'null'){
					$this->db->where_in($this->db->dbprefix('user').'.division_id ',$this->input->post('division'));
				}			
				if ($this->input->post('department') && $this->input->post('department') != 'null'){
					$this->db->where("{$this->db->dbprefix}user.department_id IN ({$department})");
				}
			}

	        $result = $this->db->get();   

			if( $this->db->_error_message() != "" ){
				$response->msg = $this->db->_error_message();
				$response->msg_type = "error";
			}
			else{        
		        $total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
		        $response->page = $page > $total_pages ? $total_pages : $page;
		        $response->total = $total_pages;
		        $response->records = $result->num_rows();                        

		        $response->msg = "";

				$this->db->select(''.$this->db->dbprefix. 'user_company_department.department_id'.' as "DepartmentId",'.$this->db->dbprefix. 'user_company_division.division_id'.' as "DivisionId"');
				$this->db->select(''.$this->db->dbprefix. 'user_company_division.division'.' as "Division",'.$this->db->dbprefix. 'user_company_department.department'.' as "Department",'.$this->db->dbprefix. 'user_position.position'.' as "Position"');
				$this->db->select('CONCAT(' . $this->db->dbprefix . 'user.firstname," ", LEFT(' . $this->db->dbprefix . 'user.middlename,1)," . ", user.lastname) as "Full Name"', false);
				$this->db->select(''.$this->db->dbprefix. 'user_rank.job_rank'.' as "Rank Range",'.$this->db->dbprefix. 'user_rank_code.job_rank_code'.' as "Rank Code"');
				$this->db->from('user');
				$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee').'.employee_id');
				$this->db->join($this->db->dbprefix('user_company_division'),$this->db->dbprefix('user').'.division_id = '.$this->db->dbprefix('user_company_division').'.division_id',"left");
				$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
				$this->db->join($this->db->dbprefix('user_position'),$this->db->dbprefix('user').'.position_id = '.$this->db->dbprefix('user_position').'.position_id',"left");		
				$this->db->join($this->db->dbprefix('user_rank_code'),$this->db->dbprefix('employee').'.rank_code = '.$this->db->dbprefix('user_rank_code').'.job_rank_code_id',"left");		
				$this->db->join('user_rank','employee.rank_id = user_rank.job_rank_id','left');
				$this->db->where("user.deleted = 0 AND {$this->db->dbprefix}employee.status_id < 3 AND ".$search);	

				if ($this->userinfo['login'] != "superadmin"){
					$this->db->where("{$this->db->dbprefix}user.department_id IN ({$department})");
				}
				else{
					if ($this->input->post('company') && $this->input->post('company') != 'null'){
						$this->db->where_in($this->db->dbprefix('user').'.company_id ',$this->input->post('company'));
					}				
					if ($this->input->post('division') && $this->input->post('division') != 'null'){
						$this->db->where_in($this->db->dbprefix('user').'.division_id ',$this->input->post('division'));
					}			
					if ($this->input->post('department') && $this->input->post('department') != 'null'){
						$this->db->where("{$this->db->dbprefix}user.department_id IN ({$department})");
					}
				}

/*		        if ($this->input->post('sidx')) {
		            $sidx = $this->input->post('sidx');
		            $sord = $this->input->post('sord');
		            $this->db->order_by($sidx . ' ' . $sord);
		            $this->db->order_by('DivisionId asc, Division asc, DepartmentId asc,Department asc'); 	        	
		        }
		        else{
					$this->db->order_by('DivisionId asc, Division asc, DepartmentId asc,Department asc'); 	        	
		        }*/

		        $this->db->order_by('DivisionId asc, Division asc, DepartmentId asc,Department asc,rank_index DESC'); 	        	

		        //$start = $limit * $page - $limit;
		       // $this->db->limit($limit, $start);        
		        
		        $result = $this->db->get();

	/*	        dbug($this->db->last_query());
		        return;*/

		        $ctr = 0;
		        $arr_container_division = array();
		        $arr_container_department = array();
		        $arr_container_department_count = array();
		        foreach ($result->result() as $row) {
				    if(!in_array($row->{'DivisionId'}, $arr_container_division, true)){
				        array_push($arr_container_division,$row->{'DivisionId'});
			            $response->rows[$ctr]['cell'][0] = "<span>" . strtoupper($row->{'Division'}) . "</span>";
			            $response->rows[$ctr]['cell'][1] = "";
			            $response->rows[$ctr]['cell'][2] = "";
			            $response->rows[$ctr]['cell'][3] = "";
			            $response->rows[$ctr]['cell'][4] = "";
			            $ctr++;

				    	if ($row->{'Department'} != NULL){
						    if(!in_array($row->{'DepartmentId'}, $arr_container_department, true)){
						        array_push($arr_container_department,$row->{'DepartmentId'});

						        $sql_query = "SELECT count(*) AS total_count FROM {$this->db->dbprefix}user u 
						        			  INNER JOIN {$this->db->dbprefix}employee e ON u.user_id = e.employee_id
						        			  LEFT JOIN {$this->db->dbprefix}user_company_department d ON u.department_id = d.department_id 
						        			  LEFT JOIN {$this->db->dbprefix}user_position up ON u.position_id = up.position_id 
						        			  WHERE u.department_id = {$row->{'DepartmentId'}} 
						        			  	AND u.division_id = {$row->{'DivisionId'}}
						        			  	AND e.status_id < 3";

					            $result = $this->db->query($sql_query);

					            $response->rows[$ctr]['cell'][0] = '<span style="padding-left:20px">' . $row->{'Department'} . '</span>';
					            $response->rows[$ctr]['cell'][1] = "";
					            $response->rows[$ctr]['cell'][2] = "";
					            $response->rows[$ctr]['cell'][3] = "";
					            $response->rows[$ctr]['cell'][4] = $result->row()->total_count;
					            $ctr++;

					            $response->rows[$ctr]['cell'][0] = '<span style="padding-left:100px">' . $row->{'Position'} . '</span>';
					            $response->rows[$ctr]['cell'][1] = str_replace(' . ', '. ', $row->{'Full Name'});
					            $response->rows[$ctr]['cell'][2] = $row->{'Rank Range'};
					            $response->rows[$ctr]['cell'][3] = $row->{'Rank Code'};
					            $response->rows[$ctr]['cell'][4] = '';
					            $ctr++;					            
					        }
					        else{
					            $response->rows[$ctr]['cell'][0] = '<span style="padding-left:100px">' . $row->{'Position'} . '</span>';
					            $response->rows[$ctr]['cell'][1] = str_replace(' . ', '. ', $row->{'Full Name'});
					            $response->rows[$ctr]['cell'][2] = $row->{'Rank Range'};
					            $response->rows[$ctr]['cell'][3] = $row->{'Rank Code'};
					            $response->rows[$ctr]['cell'][4] = '';
					            $ctr++;						        	
					        }
				    	}
				    	else{
				            $response->rows[$ctr]['cell'][0] = '<span style="padding-left:100px">' . $row->{'Position'} . '</span>';
				            $response->rows[$ctr]['cell'][1] = str_replace(' . ', '. ', $row->{'Full Name'});
				            $response->rows[$ctr]['cell'][2] = $row->{'Rank Range'};
				            $response->rows[$ctr]['cell'][3] = $row->{'Rank Code'};
				            $response->rows[$ctr]['cell'][4] = '';
				            $ctr++;			    		
				    	}		            
				    }
				    else{
				    	if ($row->{'Department'} != NULL){
						    if(!in_array($row->{'DepartmentId'}, $arr_container_department, true)){
						        array_push($arr_container_department,$row->{'DepartmentId'});			    	
						        				            
						        $sql_query = "SELECT count(*) AS total_count FROM {$this->db->dbprefix}user u 
						        			  INNER JOIN {$this->db->dbprefix}employee e ON u.user_id = e.employee_id
						        			  LEFT JOIN {$this->db->dbprefix}user_company_department d ON u.department_id = d.department_id 
						        			  LEFT JOIN {$this->db->dbprefix}user_position up ON u.position_id = up.position_id
						        			  WHERE u.department_id = {$row->{'DepartmentId'}} 
						        			  	AND u.division_id = {$row->{'DivisionId'}}
						        			  	AND e.status_id < 3";

					            $result = $this->db->query($sql_query);

					            $response->rows[$ctr]['cell'][0] = '<span style="padding-left:20px">' . $row->{'Department'} . '</span>';
					            $response->rows[$ctr]['cell'][1] = "";
					            $response->rows[$ctr]['cell'][2] = "";
					            $response->rows[$ctr]['cell'][3] = "";
					            $response->rows[$ctr]['cell'][4] = $result->row()->total_count;;
					            $ctr++;

					            $response->rows[$ctr]['cell'][0] = '<span style="padding-left:100px">' . $row->{'Position'} . '</span>';
					            $response->rows[$ctr]['cell'][1] = str_replace(' . ', '. ', $row->{'Full Name'});
					            $response->rows[$ctr]['cell'][2] = $row->{'Rank Range'};
					            $response->rows[$ctr]['cell'][3] = $row->{'Rank Code'};
					            $response->rows[$ctr]['cell'][4] = '';
					            $ctr++;					            
					        }
					        else{
					            $response->rows[$ctr]['cell'][0] = '<span style="padding-left:100px">' . $row->{'Position'} . '</span>';
					            $response->rows[$ctr]['cell'][1] = str_replace(' . ', '. ', $row->{'Full Name'});
					            $response->rows[$ctr]['cell'][2] = $row->{'Rank Range'};
					            $response->rows[$ctr]['cell'][3] = $row->{'Rank Code'};
					            $response->rows[$ctr]['cell'][4] = '';
					            $ctr++;						        	
					        }
				    	}
				    	else{
				            $response->rows[$ctr]['cell'][0] = '<span style="padding-left:100px">' . $row->{'Position'} . '</span>';
				            $response->rows[$ctr]['cell'][1] = str_replace(' . ', '. ', $row->{'Full Name'});
				            $response->rows[$ctr]['cell'][2] = $row->{'Rank Range'};
				            $response->rows[$ctr]['cell'][3] = $row->{'Rank Code'};
				            $response->rows[$ctr]['cell'][4] = '';
				            $ctr++;			    		
				    	}
				    } 	
		        }
		    }
		}

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

    function _set_listview_query($listview_id = '', $view_actions = true) {
		$this->listview_column_names = array('Position Title as of ' . date('M d, Y'), 'Name of Incumbent', 'Range of Ranks', 'Rank Code', 'Staff Count');

		$this->listview_columns = array(
				array('name' => 'position', 'width' => '600','align' => 'left'),				
				array('name' => 'firstname'),
				array('name' => 'range_rank'),
				array('name' => 'rank_code'),
				array('name' => 'staff_count')
			);                                     
    }

	function _set_search_all_query()
	{
		$value =  $this->input->post('searchString');
		$search_string = array();
		foreach($this->search_columns as $search)
		{
			$column = strtolower( $search['column'] );
			if(sizeof(explode(' as ', $column)) > 1){
				$as_part = explode(' as ', $column);
				$search['column'] = strtolower( trim( $as_part[0] ) );
			}
			$search_string[] = $search['column'] . ' LIKE "%'. $value .'%"' ;
		}
		$search_string[] = $this->db->dbprefix .'user.firstname LIKE "%' . $value . '%"';
		$search_string[] = $this->db->dbprefix .'user.lastname LIKE "%' . $value . '%"';
		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}

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

	function get_department(){
		$department = $this->db->query('SELECT * FROM ' . $this->db->dbprefix('user_company_department') . ' WHERE division_id IN ('.$this->input->post('div_id_delimited').')')->result_array();

        $html .= '<select id="department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
            foreach($department as $department_record){
                $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
            }
        $html .= '</select>';	

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}

	function export() {	
		$this->_excel_export();
	}

	private function _excel_export($record_id = 0)
	{		
		$search = 1;
		
		$department = implode(",", $this->input->post('department'));

		$this->db->select(''.$this->db->dbprefix. 'user_company_division.division_id'.' as "DivisionId",'.$this->db->dbprefix. 'user_company_department.department_id'.' as "DepartmentId"');
		$this->db->select(''.$this->db->dbprefix. 'user_company_division.division'.' as "Division",'.$this->db->dbprefix. 'user_company_department.department'.' as "Department",'.$this->db->dbprefix. 'user_position.position'.' as "Position"');
		$this->db->select('CONCAT(' . $this->db->dbprefix . 'user.firstname, " ", LEFT(' . $this->db->dbprefix . 'user.middlename,1)," . ",user.lastname) as "Full Name"', false);
		$this->db->select(''.$this->db->dbprefix. 'user_rank.job_rank'.' as "Rank Range",'.$this->db->dbprefix. 'user_rank_code.job_rank_code'.' as "Rank Code"');
		$this->db->from('user');
		$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee').'.employee_id');
		$this->db->join($this->db->dbprefix('user_company_division'),$this->db->dbprefix('user').'.division_id = '.$this->db->dbprefix('user_company_division').'.division_id',"left");
		$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
		$this->db->join($this->db->dbprefix('user_position'),$this->db->dbprefix('user').'.position_id = '.$this->db->dbprefix('user_position').'.position_id',"left");		
		//$this->db->join($this->db->dbprefix('user_rank_range'),$this->db->dbprefix('employee').'.range_of_rank = '.$this->db->dbprefix('user_rank_range').'.job_rank_range_id',"left");		
		$this->db->join($this->db->dbprefix('user_rank_code'),$this->db->dbprefix('employee').'.rank_code = '.$this->db->dbprefix('user_rank_code').'.job_rank_code_id',"left");		
		$this->db->join('user_rank','employee.rank_id = user_rank.job_rank_id','left');			
		$this->db->where("user.deleted = 0 AND {$this->db->dbprefix}employee.status_id < 3 AND ".$search);	

		if ($this->userinfo['login'] != "superadmin"){
			$this->db->where("{$this->db->dbprefix}user.department_id IN ({$department})");
		}
		else{
			if ($this->input->post('company') && $this->input->post('company') != 'null'){
				$this->db->where_in($this->db->dbprefix('user').'.company_id ',$this->input->post('company'));
			}				
			if ($this->input->post('division') && $this->input->post('division') != 'null'){
				$this->db->where_in($this->db->dbprefix('user').'.division_id ',$this->input->post('division'));
			}			
			if ($this->input->post('department') && $this->input->post('department') != 'null'){
				$this->db->where("{$this->db->dbprefix}user.department_id IN ({$department})");
			}
		}

/*        if ($this->input->post('sidx')) {
            $sidx = $this->input->post('sidx');
            $sord = $this->input->post('sord');
            $this->db->order_by($sidx . ' ' . $sord);
            $this->db->order_by('DivisionId asc, Division asc, DepartmentId asc,Department asc'); 	        	
        }
        else{
			$this->db->order_by('DivisionId asc, Division asc, DepartmentId asc,Department asc'); 	        	
        }*/

        $this->db->order_by('DivisionId asc, Division asc, DepartmentId asc,Department asc,rank_index DESC'); 	        	

		$q = $this->db->get();

		$query  = $q;
		$fields = $q->list_fields();

		//$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Employee Roster Report")
		            ->setDescription("Employee Roster Report");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);	
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);	
		//$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);					

		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		unset($fields[0]);
		unset($fields[1]);
		unset($fields[2]);
		unset($fields[3]);				
		$fields[] = "Staff Count";
		foreach ($fields as $field) {
			if ($alpha_ctr >= count($alphabet)) {
				$alpha_ctr = 0;
				$sub_ctr++;
			}

			if ($sub_ctr > 0) {
				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
			} else {
				$xcoor = $alphabet[$alpha_ctr];
			}

			$activeSheet->setCellValue($xcoor . '6', $field);

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		for($ctr=1; $ctr<6; $ctr++){

			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$activeSheet->setCellValue('A1', $this->config->item('title','meta'));
		$activeSheet->setCellValue('A2', 'Employee Roster Report');

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		// contents.
		$line = 7;
        $arr_container_division = array();
        $arr_container_department = array();		
		foreach ($query->result() as $row) {
			$sub_ctr   = 0;			
			$alpha_ctr = 0;
		    if(!in_array($row->{'DivisionId'}, $arr_container_division, true)){
		        array_push($arr_container_division,$row->{'DivisionId'});

				$objPHPExcel->getActiveSheet()->setCellValue("A" . $line, $row->{'Division'});
				$line++;

		    	if ($row->{'Department'} != NULL){
				    if(!in_array($row->{'DepartmentId'}, $arr_container_department, true)){
				        array_push($arr_container_department,$row->{'DepartmentId'});			    	
				        
				        $sql_query = "SELECT count(*) AS total_count FROM {$this->db->dbprefix}user u 
				        			  INNER JOIN {$this->db->dbprefix}employee e ON u.user_id = e.employee_id
				        			  LEFT JOIN {$this->db->dbprefix}user_company_department d ON u.department_id = d.department_id 
				        			  LEFT JOIN {$this->db->dbprefix}user_position up ON u.position_id = up.position_id 
				        			  WHERE u.department_id = {$row->{'DepartmentId'}} 
				        			  	AND u.division_id = {$row->{'DivisionId'}}
				        			  	AND e.status_id < 3";
			            $result = $this->db->query($sql_query);

						$objPHPExcel->getActiveSheet()->setCellValue("A" . $line, '               ' . $row->{'Department'});
						$objPHPExcel->getActiveSheet()->setCellValue("E" . $line, $result->row()->total_count);

						$line++;

			            $ctr = 0;
						foreach ($fields as $field) {
							if ($alpha_ctr >= count($alphabet)) {
								$alpha_ctr = 0;
								$sub_ctr++;
							}

							if ($sub_ctr > 0) {
								$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
							} else {
								$xcoor = $alphabet[$alpha_ctr];
							}

							if ($ctr == 0){
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, '                         ' . str_replace(' . ', '. ', $row->{$field}));
							}
							else{
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, str_replace(' . ', '. ', $row->{$field}));	
							}
							$ctr++;								

							$alpha_ctr++;
						}
						$line++;				            
			        }
			        else{
			        	$ctr = 0;
						foreach ($fields as $field) {
							if ($alpha_ctr >= count($alphabet)) {
								$alpha_ctr = 0;
								$sub_ctr++;
							}

							if ($sub_ctr > 0) {
								$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
							} else {
								$xcoor = $alphabet[$alpha_ctr];
							}

							if ($ctr == 0){
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, '                         ' . str_replace(' . ', '. ', $row->{$field}));
							}
							else{
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, str_replace(' . ', '. ', $row->{$field}));	
							}
							$ctr++;								

							$alpha_ctr++;
						}
						$line++;					        	
			        }
		    	}					            
		    }
		    else{
		    	if ($row->{'Department'} != NULL){
				    if(!in_array($row->{'DepartmentId'}, $arr_container_department, true)){
				        array_push($arr_container_department,$row->{'DepartmentId'});			    	

				        $sql_query = "SELECT count(*) AS total_count FROM {$this->db->dbprefix}user u 
				        			  INNER JOIN {$this->db->dbprefix}employee e ON u.user_id = e.employee_id
				        			  LEFT JOIN {$this->db->dbprefix}user_company_department d ON u.department_id = d.department_id 
				        			  LEFT JOIN {$this->db->dbprefix}user_position up ON u.position_id = up.position_id 
				        			  WHERE u.department_id = {$row->{'DepartmentId'}} 
				        			  	AND u.division_id = {$row->{'DivisionId'}}
				        			  	AND e.status_id < 3";
			            $result = $this->db->query($sql_query);

						$objPHPExcel->getActiveSheet()->setCellValue("A" . $line, '               ' . $row->{'Department'});
						$objPHPExcel->getActiveSheet()->setCellValue("E" . $line, $result->row()->total_count);
						$line++;

			            $ctr = 0;
						foreach ($fields as $field) {
							if ($alpha_ctr >= count($alphabet)) {
								$alpha_ctr = 0;
								$sub_ctr++;
							}

							if ($sub_ctr > 0) {
								$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
							} else {
								$xcoor = $alphabet[$alpha_ctr];
							}

							if ($ctr == 0){
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, '                         ' . str_replace(' . ', '. ', $row->{$field}));
							}
							else{
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, str_replace(' . ', '. ', $row->{$field}));	
							}
							$ctr++;								

							$alpha_ctr++;
						}
						$line++;				            
			        }
			        else{
			        	$ctr = 0;
						foreach ($fields as $field) {
							if ($alpha_ctr >= count($alphabet)) {
								$alpha_ctr = 0;
								$sub_ctr++;
							}

							if ($sub_ctr > 0) {
								$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
							} else {
								$xcoor = $alphabet[$alpha_ctr];
							}

							if ($ctr == 0){
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, '                         ' . str_replace(' . ', '. ', $row->{$field}));
							}
							else{
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, str_replace(' . ', '. ', $row->{$field}));	
							}
							$ctr++;								

							$alpha_ctr++;
						}
						$line++;					        	
			        }
		    	}
/*		    	else{
		    		$ctr = 0;
					foreach ($fields as $field) {
						if ($alpha_ctr >= count($alphabet)) {
							$alpha_ctr = 0;
							$sub_ctr++;
						}

						if ($sub_ctr > 0) {
							$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
						} else {
							$xcoor = $alphabet[$alpha_ctr];
						}

						if ($field == "Staff Count"){
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, '');
						}
						else{
							if ($ctr == 0){
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, '                         ' . $row->{$field});
							}
							else{
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{$field});	
							}
							$ctr++;
						}

						$alpha_ctr++;
					}
					$line++;		    		
		    	}*/
		    } 
		}

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename='.url_title("Employee Roster Report").'_'.date('Y-m-d').'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}		

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
        $buttons = "";
                
		return $buttons;
	}		
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>