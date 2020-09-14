<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Development extends CI_Controller
{
	function __construct()
   {
        parent::__construct();
		$this->load->model('hdicore');

		$this->user = $this->session->userdata('user');
		if( !$this->user ) $this->user = $this->hdicore->_verify_login( 'admin' );
		$this->module_id = 9999;
		$this->module = 'development';
		$this->module_name = 'development';
		$this->module_link = 'development';
		$this->parent_path = '';
		$data['method'] = $this->uri->rsegment(2);

		$app_directories =  $this->hdicore->_get_config('app_directories');
		$user_settings = $app_directories['user_settings_dir'] . $this->user->user_id . '.php';
		require_once( $user_settings );
		$this->userinfo = $userinfo;
		$data['header_nav'] = $navs;
		$quicklink_file = $app_directories['system_settings_dir'].'quick_links.php';
		require_once($quicklink_file);
		$data['quick_link'] = $quick_links;
		$data['footer_widget_state'] = $this->hdicore->_get_user_config('footer_widget_state', $this->user->user_id);

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->jqgrid_title = "Research and Development List";
		$this->detailview_title = 'Research and Development Info';
		$this->detailview_description = 'This page shows detailed information stuff in Research and Development';
		$this->editview_title = 'Research and Development Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about Research and Development';

		$this->load->vars( $data );
    }

	// START - default module functions
	// default jqgrid controller method

	function index()
  {
		$data['content'] = 'development/developmentview';
		$data['meta'] = $this->hdicore->_get_meta();

		$this->listview_title = 'Research and Development - Index';
		$this->listview_description = 'Ongoing test cases, stuff currently being researched and developed';

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

	function tcpdf()
    {
        $this->load->library('pdf');

        // set document information
        $this->pdf->SetSubject('TCPDF Tutorial');
        $this->pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // add a page
        $this->pdf->AddPage();

        // print a line using Cell()
        $this->pdf->writeHTML('<strong>test</strong>', true, false, true, false, '');

        //Close and output PDF document
        $this->pdf->Output('example_001.pdf', 'I');
    }

	function chat()
	{
		$data['content'] = 'development/chatview';
		$data['meta'] = $this->hdicore->_get_meta();

		$this->listview_title = 'Research and Development - Chat';
		$this->listview_description = 'Ongoing test cases, stuff currently being researched and developed';

		//load variables to env
		$this->load->vars( $data );

		//chat server
		$params["serverid"] = 'room1'; // could be the schedule id
		$params["title"] = 'Subject Discussion'; // could be the subject name
		$params['channels'] = array(
			'Test Room',
			'HDI SysTech',
			'SysTech Lunchmen'
		);
		$params['data_public_url'] = base_url().'phpfreechat/data/public';
		$params['server_script_url'] = base_url().'development/chat';
		$params['theme'] = "green";
		$params['admins'] = array('admin' => 'password');
		$params['display_pfc_logo'] = false;

		//chat user
		$params['nick'] = $this->userinfo['lastname'].', '.$this->userinfo['firstname'];
		$params["frozen_nick"] = true;

		//chat db config
		$params['container_type'] = 'mysql';
		$params['container_cfg_mysql_host'] = $this->db->hostname;
		$params['container_cfg_mysql_port'] = $this->db->port;
		$params['container_cfg_mysql_database'] = $this->db->database ;
		$params['container_cfg_mysql_table'] = $this->db->dbprefix . 'chat';
		$params['container_cfg_mysql_username'] = $this->db->username ;
		$params['container_cfg_mysql_password'] = $this->db->password ;

		$this->load->library('chat', $params);

		//load the final view
		//load header
		$this->load->view( $this->userinfo['rtheme'].'/template/header' );
		$this->load->view( $this->userinfo['rtheme'].'/template/header-nav' );

		//load page content
		$this->load->view( $this->userinfo['rtheme'].'/template/page-content' );

		//load footer
		$this->load->view( $this->userinfo['rtheme'].'/template/footer' );
	}

	function jpegcam(){
		$data['content'] = 'development/jpegcamview';
		$data['meta'] = $this->hdicore->_get_meta();
		$data['scripts'][] = jpegcam_script();

		$this->listview_title = 'Research and Development - JPEGCAM';
		$this->listview_description = 'Ongoing test cases, stuff currently being researched and developed';

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

	function webcam_snapshot(){
		header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
		header("Cache-Control: no-cache, must-revalidate" );
		header("Pragma: no-cache" );

		$filename = 'uploads/webcam_snapshots/'. $this->user->user_id . '.jpg';
		if(file_exists($filename)) unlink($filename);
		$result = file_put_contents( $filename, file_get_contents('php://input') );
		if (!$result) {
			print "ERROR: Failed to write data to $filename, check permissions\n";
			exit();
		}

		$url = base_url() . $filename.'?lastmodidied='.date('YmdHis');
		print "$url\n";
	}

	function scribd_upload(){
		require(APPPATH.'config/scribd'.EXT);
		$this->load->library('scribd');
		if(file_exists('uploads/BadLight.pdf')){
			//$this->scribd->login($scribd['username'], $scribd['password']);
			$response = $this->scribd->upload(FCPATH.'uploads\BadLight.pdf');
			dbug( $response );
		}
	}

	function insert_cities() {
$philippine_cities = array("Alaminos City","Angeles City","Antipolo City","Bacolod City","Bago City","Baguio City","Bais City","Balanga City","Batangas City","Bayawan City","Bisilig City","Butuan City","Cabanatuan City","Cadiz City","Cagayan de Oro City","Calamba City","Calapan City","Calbayog City","Caloocan City","Candon City","Canlaon City","Cauayan City","Cavite City","Cebu City","Cotabato City","Dagupan City","Danao City","Dapitan City","Davao City","Digos City","Dipolog City","Dumaguete City","Escalante City","Gapan City","General Santos City","Gingoog City","Himamaylan City","Iligan City","Iloilo City","Iriga City",
"Isabela City","Island Garden City of Samal","Kabankalan City","Kidapawan City","Koronadal City","La Carlota City","Laoag City","Lapu-Lapu City","Las Piñas City","Legazpi City","Ligao City","Lipa City","Lucena City","Maasin City","Makati City","Malabon City","Malaybalay City","Malolos City","Malolos City","Mandaluyong City","Mandaue City","Manila","Maragondon","Marawi City","Masbate City","Muntinlupa City","Naga City","Olongapo City","Ormoc City","Oroquieta City","Ozamis City","Pagadian City","Palayan City","Legazpi City","Parañaque City","Pasay City","Pasig City","Passi City","Puerto Princesa City","Quezon City","Roxas City","Sagay City","San Carlos City, Negros Occidental","San Carlos City, Pangasinan","San Fernando City, La Union","San Fernando City, Pampanga","San Jose City","San Jose del Monte City","San Pablo City","Santa Rosa City","Santiago City","Muñ City","Silay City","Sipalay City","Sorsogon City","Surigao City","Tabaco City","Tacloban City","Tacurong City",
"Tagaytay City","Tagbilaran City","Tagum City","Talisay City, Cebu", "Talisay City, Negros Occidental","Tanauan City","Tangub City","Tanjay City","Tarlac City","Taguig City","Toledo City","Trece Martires City","Tuguegarao City","Urdaneta City","Valencia City","Valenzuela City","Victorias City","Vigan City","Zamboanga City");		
	
		foreach ($philippine_cities as $city) {
			$cities[]['city'] = $city;
		}
		$this->db->insert_batch('cities', $cities);
	}

	function test_dtr_upload()
	{
		$this->load->helper('time_upload');
		process_time_raw(1, 1);
	}
}

/* End of file */
/* Location: system/application */