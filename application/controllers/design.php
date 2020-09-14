<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Design extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('hdicore');

		$this->user = $this->hdicore->_verify_login('admin');
		$this->load->model('hdicore');
		
		$this->module_id = 9999;
		$this->module = 'design';
		$this->module_name = 'design';
		$this->module_link = 'module_link';
		$this->parent_path = '';
		$data['method'] = $this->uri->rsegment(2);

		$app_directories = $this->hdicore->_get_config('app_directories');
		$user_settings = $app_directories['user_settings_dir'] . $this->session->userdata['user']->user_id . '.php';

		require_once( $user_settings );
		eval($this->encrypt->decode($config_hash));
		
		$this->userinfo = (array) $this->hdicore->_get_userinfo( $this->session->userdata['user']->user_id  );

		$data['header_nav'] = $navs;
		$quicklink_file = $app_directories['system_settings_dir'] . 'quick_links.php';
		require_once($quicklink_file);
		$data['quick_link'] = $quick_links;
		$data['meta'] = $this->hdicore->_get_meta();
		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->jqgrid_title = "Designs List";
		$this->detailview_title = 'Module Info';
		$this->detailview_description = 'This page shows detailed information about a particular module';
		$this->editview_title = 'Module Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about modules';

		$this->load->vars($data);
	}

	// START - default module functions
	// default jqgrid controller method

	function index() {
		$data['content'] = 'design/designview';

		$this->listview_title = 'Design - Index';
		$this->listview_description = 'List of all design(s) organized and group together for easier browsing. ( please supply your description hehehe ).';
		
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

	function buttons() {
		$data['content'] = 'design/buttons';

		$this->listview_title = 'Design - Buttons';
		$this->listview_description = 'This module shows all button designs, its html structure and required classes/ids if needed.';

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footers
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function tester() {
		$data['content'] = 'design/tester';

		$this->listview_title = 'Design - Tester';
		$this->listview_description = 'This module shows all button designs, its html structure and required classes/ids if needed.';

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footers
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function forms() {
		$data['content'] = 'design/forms';

		$this->listview_title = 'Design - Forms';
		$this->listview_description = 'This module shows all button designs, its html structure and required classes/ids if needed.';

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footers
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function applicantview() {
		$data['content'] = 'design/applicantview';

		$this->listview_title = 'Design - Applicant View';
		$this->listview_description = '';

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footers
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function candidates() {
		$data['content'] = 'design/candidates';

		$this->listview_title = 'Design - Candidates';
		$this->listview_description = '';

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footers
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function postedjobs() {
		$data['content'] = 'design/postedjobs';

		$this->listview_title = 'Design - Posted Jobs';
		$this->listview_description = '';

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footers
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function application() {
		$data['content'] = 'design/application';

		$this->listview_title = 'Design - Application';
		$this->listview_description = '';

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footers
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function position() {
		$data['content'] = 'design/position';

		$this->listview_title = 'Design - Position';
		$this->listview_description = '';

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footers
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function manpower() {
		$data['content'] = 'design/manpower';

		$this->listview_title = 'Design - Manpower Request';
		$this->listview_description = '';

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footers
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function preemployment() {
		$data['content'] = 'design/preemployment';

		$this->listview_title = 'Design - Pre-employment';
		$this->listview_description = '';

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footers
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function worksched() {
		$data['content'] = 'design/worksched';

		$this->listview_title = 'Design - Work Sched';
		$this->listview_description = '';

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footers
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function adu() {
		$data['content'] = 'adu/adu';

		$data['scripts'][] = jquerttimers_script();

		if ($this->session->flashdata('flashdata')) {
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'] . '/template/flashdata', $info, true);
		}

		$this->listview_title = 'Welcome to Adamson University eLearning';
		$this->listview_description = '';


		//load variables to env
		$this->load->vars($data);

		//load header
		$this->load->view($this->userinfo['rtheme'] . '/header');
		$this->load->view($this->userinfo['rtheme'] . '/header-nav');

		//load the left sidebar
		$this->load->view($this->userinfo['rtheme'] . '/left-sidebar');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/page-content');

		//load the right sidebar
		$this->load->view($this->userinfo['rtheme'] . '/right-sidebar');

		//load footer
		$this->load->view($this->userinfo['rtheme'] . '/footer');
	}

	function viewtemplate($template_id) {
		$this->load->model('template');

		$template = $this->template->get_template($template_id);

		echo $template['body'];
	}

	function employee_details() {
		$data['content'] = 'design/employee_details';

		$this->listview_title = 'Design - Employee details View';
		$this->listview_description = '';

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footers
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}
	
	
	function appform() {
		$data['content'] = 'design/appform';

		$this->listview_title = 'Design - External Application Form';
		$this->listview_description = '';

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/design/appform');
	}

	function calendar() {
		$prefs['template'] = '

			   {table_open}<table border="0" cellpadding="0" cellspacing="0">{/table_open}

			   {heading_row_start}<tr>{/heading_row_start}

			   {heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
			   {heading_title_cell}<th colspan="{colspan}">{heading}</th>{/heading_title_cell}
			   {heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}

			   {heading_row_end}</tr>{/heading_row_end}

			   {week_row_start}<tr>{/week_row_start}
			   {week_day_cell}<td>{week_day}</td>{/week_day_cell}
			   {week_row_end}</tr>{/week_row_end}

			   {cal_row_start}<tr>{/cal_row_start}
			   {cal_cell_start}<td>{/cal_cell_start}

			   {cal_cell_content}<a href="{content}">{day}</a>{/cal_cell_content}
			   {cal_cell_content_today}<div class="highlight"><a href="{content}">{day}</a></div>{/cal_cell_content_today}

			   {cal_cell_no_content}{day}{/cal_cell_no_content}
			   {cal_cell_no_content_today}<div class="highlight">{day}</div>{/cal_cell_no_content_today}

			   {cal_cell_blank}&nbsp;{/cal_cell_blank}

			   {cal_cell_end}</td>{/cal_cell_end}
			   {cal_row_end}</tr>{/cal_row_end}

			   {table_close}</table>{/table_close}
			';

		$data = array(
			       3  => 'http://example.com/news/article/2006/03/',
			       7  => 'http://example.com/news/article/2006/07/',
			       13 => 'http://example.com/news/article/2006/13/',
			       26 => 'http://example.com/news/article/2006/26/'
			     );		
		
		$this->load->library('calendar', $prefs);

		echo $this->calendar->generate('','',$data);
		exit();
	}

	function event_frequency() {
		$this->load->vars(array('content' => 'design/event_frequency'));

		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footers
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');		
	}
}

/* End of file */