<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Time_upload extends CI_Controller
{
	public $userinfo = array();
	public $locationinfo = array();
	public function __contruct()
	{		
		parent::__contruct();				
	}

	function getfile()
	{		
		$data['extraHeadContent'] = '
									<script src="http://localhost/hris_new/lib/jquery/jquery-1.7.1.min.js" type="text/javascript"></script>
									<script src="http://localhost/hris_new/lib/jquery/jquery-ui-1.8.14.custom.min.js" type="text/javascript"></script>
									<script src="http://localhost/hris_new/lib/jquery/jquery.datepick.timepicker.addon.js" type="text/javascript"></script>
									<script src="http://localhost/hris_new/lib/jquery/jquery.blockUI.js" type="text/javascript"></script>
									<script src="http://localhost/hris_new/lib/boxy0.1.4/javascripts/jquery.boxy.js" type="text/javascript"></script>
									<link media="screen" href="http://localhost/hris_new/lib/boxy0.1.4/stylesheets/boxy.css" type="text/css" rel="stylesheet"></link>
									<script src="http://localhost/hris_new/lib/tipsy1.0.0a/javascripts/jquery.tipsy.js" type="text/javascript"></script>
									<link href="http://localhost/hris_new/lib/tipsy1.0.0a/stylesheets/tipsy.css" rel="stylesheet" type="text/css"></link>
									<script src="http://localhost/hris_new/lib/qtip/jquery.qtip-1.0.0-rc3.min.js" type="text/javascript"></script>
									<script type="text/javascript" src="http://localhost/hris_new/lib/jqGrid-4.1.2/js/i18n/grid.locale-en.js"></script>
									<script type="text/javascript" src="http://localhost/hris_new/lib/jqGrid-4.1.2/js/jquery.jqGrid.min.js"></script>
									<script type="text/javascript" src="http://localhost/hris_new/lib/modules/time_upload.js"></script>';
		$this->load->helper(array('form', 'url'));
		$this->load->vars($data);
        $this->load->view("time_upload");
	}

	function time_browse()
	{
		$this->load->helper(array('form', 'url'));
		$response->entry_form = $error
								.form_open_multipart('time_upload/do_upload','id=dtr_upload')."
								<input type='file' name='userfile' id='userfile' size='20' />
								<br /><br />
								<select id='type_of_file' name='type_of_file'>
									<option value='0'>Select...</option>
									<option value='1'>HEAD OFFICE(txt)</option>
									<option value='2'>HEAD OFFICE(dat)</option>
								</select>
								<br /><br />
								<input type='button' id='upload_now' value='upload'/>";
		$data['json'] = $response;
		$this->load->view('template/ajax', $data);
	}

	function do_upload()	
	{
		$val = 0;
		$name = substr($_FILES['userfile']['name'],-3);
		if($_POST['type_of_file'] == 1)
		{
			if($name != 'txt')
			{
				$val = 1;
				$return = 'file is not valid!';
			}
		}
		if($_POST['type_of_file'] == 2)
		{
			if($name != 'dat')
			{
				$val = 1;
				$return = 'file is not valid!';
			}
		}
		if($val == 0)
		{
			$this->userinfo['user_id'] = 1;		
			$this->locationinfo['location_id'] = $_POST['type_of_file'];
			$this->load->helper('time_upload');
			$return = do_time_upload();
		}
		echo $return.'<br/><a href="getfile">back to main screen</a>';
	}

	function process()
	{

	}
}