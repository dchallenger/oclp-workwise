<?php

if (!defined('ASSET_PATH')) { define('ASSET_PATH', 'themes/');}

function jpegcam_script(){
	return '<script src="'.base_url().'lib/jpegcam/webcam.js" type="text/javascript"></script>';
}

function jqgrid_listview(){
	return '<link rel="stylesheet" type="text/css" media="screen" href="'.base_url().'lib/jqGrid-4.4.1/css/ui.jqgrid.css" />
	<script src="'.base_url().'lib/jqGrid-4.4.1/js/i18n/grid.locale-en.js" type="text/javascript"></script>
	<script src="'.base_url().'lib/jqGrid-4.4.1/js/jquery.jqGrid.min.js" type="text/javascript"></script>
	<script src="'.base_url().'lib/js/listview.js" type="text/javascript"></script>';
}

function jqgrid_in_boxy(){
	return '<link rel="stylesheet" type="text/css" media="screen" href="'.base_url().'lib/jqGrid-4.4.1/css/ui.jqgrid.css" />
	<script src="'.base_url().'lib/jqGrid-4.4.1/js/i18n/grid.locale-en.js" type="text/javascript"></script>
	<script src="'.base_url().'lib/jqGrid-4.4.1/js/jquery.jqGrid.min.js" type="text/javascript"></script>
	<script src="'.base_url().'lib/js/listview_in_boxy.js" type="text/javascript"></script>';
}

function jquerttimers_script(){
	return '<script type="text/javascript" src="'.base_url().'lib/jquery/jquery.timers-1.2.js"></script>';
}


function multiselect_script()
{
	return '<link rel="stylesheet" href="'.base_url().'lib/multiselect1.10/jquery.multiselect.css"/>
	<link rel="stylesheet" href="'.base_url().'lib/multiselect1.10/jquery.multiselect.filter.css"/>
	<script type="text/javascript" charset="utf-8" src="'.base_url().'lib/multiselect1.10/src/jquery.multiselect.min.js"></script>
	<script type="text/javascript" charset="utf-8" src="'.base_url().'lib/multiselect1.10/src/jquery.multiselect.filter.min.js"></script>
	';
}

function uploadify_script()
{
	return '<link href="'.base_url().'lib/uploadify214/uploadify.css" type="text/css" rel="stylesheet" />
		<script type="text/javascript" src="'.base_url().'lib/uploadify214/swfobject.js"></script>
		<script type="text/javascript" src="'.base_url().'lib/uploadify214/jquery.uploadify.v2.1.4.min.js"></script>
		<script type="text/javascript" src="'.base_url().'lib/swfobject/swfobject.js"></script>
	';
}

function CKEditor_script()
{
	return '<script src="'.base_url().'lib/ckeditor3.6.1/ckeditor.js" type="text/javascript"></script>
	<script type="text/javascript" src="'.base_url().'lib/ckeditor3.6.1/adapters/jquery.js"></script>
	<script type="text/javascript" src="'.base_url().'lib/ckfinder2.1.1/ckfinder.js"></script>';
}

function chosen_script()
{
	return '<link rel="stylesheet" href="'.base_url().'lib/chosen/chosen.css"/>
	<script type="text/javascript" charset="utf-8" src="'.base_url().'lib/chosen/chosen.jquery.js"></script>
	<script type="text/javascript" charset="utf-8" src="'.base_url().'lib/chosen/chosen.hdi.js"></script>';
}

function jOrgChart_script()
{
	return '<link rel="stylesheet" href="'.base_url().'lib/jOrgChart/css/jquery.jOrgChart.css"/>
	<script type="text/javascript" charset="utf-8" src="'.base_url().'lib/jOrgChart/jquery.jOrgChart.js"></script>';
}

function get_in_range($val = 0) 
{
	$rate = 0;

	if ($val != '') {
		switch ($val) {
			case ($val > 0 && $val < 65):
				$rate = 1;
				break;
			case ($val >= 65 && $val < 80):
				$rate = 2;
				break;		
			case ($val >= 80 && $val < 95):
				$rate = 3;
				break;	
			case ($val >= 95 && $val < 110):
				$rate = 4;
				break;									
			case ($val >= 110):
				$rate = 5;
				break;			
			default:
				$rate = 0;
				break;
		}
	}
	
	return $rate;
}

function dbug( $arr, $return = false )
{
	$debug = "<pre>";
	$debug .= print_r( $arr, true );
	$debug .= "</pre>";

	if( $return )
		return $debug;
	else
		echo $debug;
}

function message_box($type, $msg, $visible=0, $onclick = '', $theme = 'themes/default')
{
    switch($type){
        case 'attention':
            $m['icon'] = base_url().$theme.'/images/exclamation.png';
            $m['text'] = 'Attention!';
            break;
        case 'error':
            $m['icon'] = base_url().$theme.'/images/cross-circle.png';
            $m['text'] = 'Error!';
            break;
        case 'info':
            $m['icon'] = base_url().$theme.'/images/information.png';
            $m['text'] = 'Tip!';
            break;
        case 'success':
            $m['icon'] = base_url().$theme.'/images/tick-circle.png';
            $m['text'] = 'Success!';
            break;
    }
    $m['message'] =  ( is_array($msg)? '<br />'.implode("<br />", $msg) : '&nbsp;'.$msg );

    return '
        <div id="message_box" class="'.$type.'" '.(empty($visible) ? 'style="display:none"':'').'>
            <img src="'.$m['icon'].'" alt="" >
            <p><strong>'. $m['text']. '</strong>'.$m['message'].'</p>
            <div class="close-msg" '.(empty($onclick)? '' : 'onclick="'.$onclick.'"').'></div>
        </div>';
}

function create_child_nav( $child_nav, $theme , $current_module, $depth, $nav=''){
	$child_nav_size = sizeof($child_nav);
	$child_ctr = 0;
    $visible_count = 0;
    
    // Get if this nav has visible children.
    foreach ($child_nav as $child) {
        if ($child['is_visible'] != 1) {
            $visible_count++;
        }
    }
    // No visible children, return blank to avoid creating the unnecessary <ul></ul>.
    if ($visible_count == count($child_nav)) {
        return '';
    }

    $nav_string = '<ul short_name="'.$nav.'"';
    if( $nav=='Master' || $nav=='Analytics'){
    	$nav_string .= ' style="left:auto;right:0px;"';
    }
    $nav_string .= '>';
	
	foreach($child_nav as $module_id => $child) :     
		$class_last_child = ( $child_nav_size - 1 ) == $child_ctr ? "last-nav" : '';
		if ( $child['is_visible'] == 1 && $child['access']['visible'] == 1 ) :
			$nav_string .= '<li class="'. $class_last_child .' '. ( $current_module == $module_id ? 'current-child' : ''  ) .'" depth="'. $depth .'">';
			$nav_string .= '<a href="'. (($child['link'] == '#' || empty($child['link'])) ? 'javascript:void(0)' : base_url().$child['link']) .'"><img src="'. base_url() . $theme.'/icons/'. ( !empty($child['sm_icon']) ? $child['sm_icon'] : 'icon-default-16.png' ) .'" />'. ( $child['show_icon_only'] == 1 ? '' : $child['short_name'] ) .'</a>';
			if( !empty( $child['child'] ) ) $nav_string .= create_child_nav( $child['child'], $theme, $current_module, ( $depth + 1 ) );
			$nav_string .= '</li>';
		endif;
		$child_ctr++;
	endforeach;
	$nav_string .= '</ul>';
	return $nav_string;
}

function recaptcha_script()
{
	return '<script type="text/javascript" src="http://www.google.com/recaptcha/api/js/recaptcha_ajax.js"></script>';
}

function show_access_gui( $module, $module_access, $actionlist, $is_child = FALSE, $parent_id = array()){ 
	$class = '';
	if ($is_child) {
		$class = 'child-' . $parent_id[count($parent_id) - 1];
		
		foreach ($parent_id as $id) {
			$class .= ' ' . $id . ' ';
		}
	}
	?>
    <tr class="<?=$class?> <?=is_array($module['children']) ? 'parent' : '';?>" id="<?=is_array($module['children']) ? $module['module_id'] : '';?>">
        <th class="text-left" style="border-top: none">
			<?php for( $i = 1; $i<= $module['depth']; $i++){ echo '<span class="spacing">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>'; } ?>
			<?=is_array($module['children']) ? '<span class="trigger">+</span>' : ''?> <span class="module-name"><?=$module['name']?></span>
        </th><?php
        foreach( $actionlist as $action_index => $action): ?>
            <td class="text-center <?=($action_index % 2 == 0 ? "even" : "odd")?> " style="vertical-align:middle"><?php
                if( isset($module_access[$module['module_id']][$action['action']]) && $module_access[$module['module_id']][$action['action']] == 1 )
                    $checked = 'checked="checked"';
                else
                    $checked = ''; ?>
                <input class="cb-<?=$action['action']?>" type="checkbox" name="<?=$action['action']?>[<?=$module['module_id']?>]" value="1" <?=$checked?>/>
            </td> <?php
		endforeach; ?>
    </tr> <?php
	if( is_array($module['children']) ){
		foreach($module['children'] as $child):
			array_push($parent_id, $module['module_id']);
			show_access_gui( $child, $module_access, $actionlist, TRUE, $parent_id);
		endforeach;
	}	
}

if (!function_exists('format_header')) {
    function format_header($val) {
        return ucfirst(str_replace('_', ' ', $val));
    }
}


function int_to_month( $int = 0, $full = false )
{
	switch($int)
	{
		case 1:
			return $full ? "January" : "Jan";
			break;
		case 2:
			return $full ? "February" : "Feb";
			break;
		case 3:
			return $full ? "March" : "Mar";
			break;
		case 4:
			return $full ? "April" : "Apr";
			break;
		case 5:
			return $full ? "May" : "Mar";
			break;
		case 6:
			return $full ? "June" : "Jun";
			break;
		case 7:
			return $full ? "July" : "Jul";
			break;
		case 8:
			return $full ? "August" : "Aug";
			break;
		case 9:
			return $full ? "Semptember" : "Sep";
			break;
		case 10:
			return $full ? "October" : "Oct";
			break;
		case 11:
			return $full ? "November" : "Nov";
			break;
		case 12:
			return $full ? "December" : "Dec";
			break;
		default:
			return false;
	}
}

function int_to_day( $int = 0, $type ){
	switch($int){
		case 1:
			switch($type){
				case "full":
					return "Sunday";
					break;
				case "abbreviation":
					return "Sun";
					break;
				case "number":
					return "01";
				default:
					return $int;			
			}
			break;
		case 2:
			switch($type){
				case "full":
					return "Monday";
					break;
				case "abbreviation":
					return "Mon";
					break;
				case "number":
					return "02";
				default:
					return $int;			
			}
			break;
		case 3:
			switch($type){
				case "full":
					return "Tuesday";
					break;
				case "abbreviation":
					return "Tue";
					break;
				case "number":
					return "03";
				default:
					return $int;			
			}
			break;
		case 4:
			switch($type){
				case "full":
					return "Wednesday";
					break;
				case "abbreviation":
					return "Wed";
					break;
				case "number":
					return "04";
				default:
					return $int;			
			}
			break;
		case 5:
			switch($type){
				case "full":
					return "Thursday";
					break;
				case "abbreviation":
					return "Thu";
					break;
				case "number":
					return "05";
				default:
					return $int;			
			}
			break;
		case 6:
			switch($type){
				case "full":
					return "Friday";
					break;
				case "abbreviation":
					return "Fri";
					break;
				case "number":
					return "06";
				default:
					return $int;			
			}
			break;
		case 7:
			switch($type){
				case "full":
					return "Saturday";
					break;
				case "abbreviation":
					return "Sat";
					break;
				case "number":
					return "07";
				default:
					return $int;			
			}
			break;
		default:
			return false;	
	}
}

function day_to_int( $day = "", $trailing_zero = true )
{
	switch($day)
	{
		case "Sun":
			return $trailing_zero ? '01' : '1';
			break;
		case "Sunday":
			return $trailing_zero ? '01' : '1';
			break;
		case "Mon":
			return $trailing_zero ? '02' : '2';
			break;
		case "Monday":
			return $trailing_zero ? '02' : '2';
			break;
		case "Tue":
			return $trailing_zero ? '03' : '3';
			break;
		case "Tuesday":
			return $trailing_zero ? '03' : '3';
			break;
		case "Wed":
			return $trailing_zero ? '04' : '4';
			break;
		case "Wednesday":
			return $trailing_zero ? '04' : '4';
			break;
		case "Thu":
			return $trailing_zero ? '05' : '5';
			break;
		case "Thursday":
			return $trailing_zero ? '05' : '5';
			break;
		case "Fri":
			return $trailing_zero ? '06' : '6';
			break;
		case "Friday":
			return $trailing_zero ? '06' : '6';
			break;
		case "Sat":
			return $trailing_zero ? '07' : '7';
			break;
		case "Saturday":
			return $trailing_zero ? '07' : '7';
			break;
	}
}

function month_to_int( $month = "", $trailing_zero = true )
{
	switch($month)
	{
		case "Jan":
			return $trailing_zero ? '01' : '1';
			break;
		case "January":
			return $trailing_zero ? '01' : '1';
			break;
		case "Feb":
			return $trailing_zero ? '02' : '2';
			break;
		case "February":
			return $trailing_zero ? '02' : '2';
			break;
		case "Mar":
			return $trailing_zero ? '03' : '3';
			break;
		case "March":
			return $trailing_zero ? '03' : '3';
			break;
		case "Apr":
			return $trailing_zero ? '04' : '4';
			break;	
		case "April":
			return $trailing_zero ? '04' : '4';
			break;
		case "May":
			return $trailing_zero ? '05' : '5';
			break;
		case "Jun":
			return $trailing_zero ? '06' : '6';
			break;	
		case "June":
			return $trailing_zero ? '06' : '6';
			break;
		case "Jul":
			return $trailing_zero ? '07' : '7';
			break;	
		case "July":
			return $trailing_zero ? '07' : '7';
			break;
		case "Aug":
			return $trailing_zero ? '08' : '8';
			break;	
		case "August":
			return $trailing_zero ? '08' : '8';
			break;
		case "Sep":
			return $trailing_zero ? '09' : '9';
			break;	
		case "September":
			return $trailing_zero ? '09' : '9';
			break;
		case "Oct":
			return $trailing_zero ? '10' : '10';
			break;	
		case "October":
			return $trailing_zero ? '10' : '10';
			break;
		case "Nov":
			return $trailing_zero ? '11' : '11';
			break;	
		case "November":
			return $trailing_zero ? '11' : '11';
			break;
		case "Dec":
			return $trailing_zero ? '12' : '12';	
		case "December":
			return $trailing_zero ? '12' : '12';
			break;
	}
}

/**
 * Pads a number with leading zeroes.
 * 0 => 0000
 * 1 => 0001
 * 20 => 0020
 * 432 => 0432
 * 
 * @param int $number
 * @param int $n How many 0's you want
 * @return int 
 */
function number_pad($number,$n = 4) {
	return str_pad((int) $number,$n,"0",STR_PAD_LEFT);
}

if (!function_exists('get_record_detail_array')) {
    
    /**
     *
     * Rebuild the data retrieved from MY_Controller::_record_detail()
     * removes the fieldgroups and gets only the field values.
     * 
     * @param int $record_id 
     * @param bool $raw_flag Set wether to getFieldValue or the fields value only.
     * 
     * @return mixed FALSE if no record found.
     */
    function get_record_detail_array($record_id, $raw_flag = false) {
        $ci =& get_instance();
        
        if ($ci->_record_exist($record_id)->exist) {
            $record = $ci->_record_detail($record_id);

            $ci->load->model('uitype_detail');

            $values = array();
            foreach ($record as $fieldgroup) {
                if (isset($fieldgroup['fields']) && count($fieldgroup['fields']) > 0) {
                    foreach ($fieldgroup['fields'] as $field) {
                        if ($raw_flag) {
                            $value = $field['value'];
                        } else {
                            $value = $ci->uitype_detail->getFieldValue($field);
                        }
                        
                        $values[$field['column']] = $value;
                    }
                }
            }    
            
            if (count($values) > 0) {
                return $values;
            }
        } 
            
        return FALSE;       
    }
}

if (!function_exists('get_image_thumb')) {
	/**
	 * Returns the thumbnail path equivalent of a given image.
	 * 
	 * @param string $image_path 
	 * 
	 * @return string The thumbnail path.
	 */
	function get_image_thumb($image_path) {
		$orig_path = explode('/', $image_path);
		$orig_path[0] .= '/thumbs';
		
		return implode('/', $orig_path);
	}	
}

if (!function_exists('css_path')) {
	/**
	 * Returns the css depending on the current theme, if none exists fallback to default.
	 * 
	 * @param string
	 * 
	 * @return string
	 */
	
	function css_path($file = '') {
		$ci =& get_instance();

		if (file_exists(ASSET_PATH . $ci->userinfo['rtheme'] . '/' . $file)) {
			return site_url(ASSET_PATH . $ci->userinfo['rtheme'] . '/' . $file);
		} elseif (file_exists(ASSET_PATH . $ci->config->item('default_theme') . '/' . $file)){
			return site_url(ASSET_PATH . $ci->config->item('default_theme') . '/' . $file);
		} else {
			return site_url(ASSET_PATH . $file);
		}
	}
}

if (!function_exists('get_branding')) {
	/**
	 * Returns the branding, determined whether use_logo is set to "Yes" on system config.
	 * 	 	 
	 * @return string
	 */
	
	function get_branding() {
		$ci =& get_instance();

		$meta = $ci->config->item('meta');
		
		if ($meta['use_logo']) {
			$ci->load->helper('html');

			return img($meta['logo']);
		} else {
			return $meta['title'];
		}
	}
}

if (!function_exists('create_subnav')) {
    function create_side_nav($nav, $child = false) {
        if (count($nav) <= 0) {
            return FALSE;
        }
        
        $ci =& get_instance();

        $sidenav = '';
        if ($child) {
        	$sidenav = '<ul>';
        } 

        foreach ($nav as $module_id => $user_nav) :
            if ($user_nav['is_visible'] == 1 && $user_nav['access']['visible'] == 1) {
                $class = '';
                if ($module_id == $ci->module_id) {
                    $class = '';//class="side-nav-active"';
                }

                $wstring = explode("\n", wordwrap($user_nav['short_name'], 20, "\n") );                

                $ltext = $wstring[0];
                $css_style="width:90%;margin-left:0%;cursor:pointer;";
                $sidenav .= "<li><a id='unfold-me' href=\"javascript:void(0)\"><div onclick='open_me(\"".$user_nav['link']."\")'>".$user_nav['short_name']."</div></a>";
                if (!empty($user_nav['child'])) :
                    $sidenav .= create_side_nav($user_nav['child'], true);
                endif;
                $sidenav .= '</li>';
            }
        endforeach;

        $sidenav .= '';
        if ($child) {
        	$sidenav .= '</ul>';
        } 

        return $sidenav;
    }
}

if (!function_exists('get_age')) {
	/**
	 * Return age.
	 * 
	 * @param datetime $dob	
	 * @return float
	 */
	function get_age( $dob ) {
		$dob = strtotime($dob);
		$secs = time() - $dob;
		
		return floor($secs / 31536000);
	}

}

//display field, wether to add class hidden or not
function display_field( $view = '', $display = 1 ){
	if(!empty( $view )){
		switch( $display ){
			case 1:
				return true;
				break;
			case 2:
				if( $view == 'detail' ) return true;
				break;
			case 3:
				if( $view == 'edit' ) return true;
				break;
			case 4:
			default:
				return false;
				
		}
	}
	
	return false;
}

function sanitize($text) {
	$text = htmlspecialchars($text, ENT_QUOTES);
	$text = str_replace("\n\r","\n",$text);
	$text = str_replace("\r\n","\n",$text);
	$text = str_replace("\n","<br>",$text);
	return $text;
}

/** override origial date_helper timespan function **/
function timespan($seconds = 1, $time = '', $get_days = FALSE)
{
	$CI =& get_instance();
	$CI->lang->load('date');

	if ( ! is_numeric($seconds))
	{
		$seconds = 1;
	}

	if ( ! is_numeric($time))
	{
		$time = time();
	}

	if ($time <= $seconds)
	{
		$seconds = 1;
	}
	else
	{
		$seconds = $time - $seconds;
	}

	$str = '';
	$years = floor($seconds / 31536000);

	if ($years > 0)
	{
		$str .= $years.' '.$CI->lang->line((($years	> 1) ? 'date_years' : 'date_year')).', ';
	}

	$seconds -= $years * 31536000;

	$true_days = floor($seconds / 86400);

	$months = floor($seconds / 2628000);

	if ($years > 0 OR $months > 0)
	{
		if ($months > 0)
		{
			$str .= $months.' '.$CI->lang->line((($months	> 1) ? 'date_months' : 'date_month')).', ';
		}

		$seconds -= $months * 2628000;
	}

	$weeks = floor($seconds / 604800);

	if ($years > 0 OR $months > 0 OR $weeks > 0)
	{
		if ($weeks > 0)
		{
			$str .= $weeks.' '.$CI->lang->line((($weeks	> 1) ? 'date_weeks' : 'date_week')).', ';
		}

		$seconds -= $weeks * 604800;
	}

	$days = floor($seconds / 86400);

	if ($months > 0 OR $weeks > 0 OR $days > 0)
	{
		if ($days > 0)
		{
			$str .= $days.' '.$CI->lang->line((($days	> 1) ? 'date_days' : 'date_day')).', ';
		}

		$seconds -= $days * 86400;
	}

	$hours = floor($seconds / 3600);

	if ($days > 0 OR $hours > 0)
	{
		if ($hours > 0)
		{
			$str .= $hours.' '.$CI->lang->line((($hours	> 1) ? 'date_hours' : 'date_hour')).', ';
		}

		$seconds -= $hours * 3600;
	}

	$minutes = floor($seconds / 60);



	if (!$get_days) {
		return substr(trim($str), 0, -1);
	} else {
		return array('text' => substr(trim($str), 0, -1), 'days' => $true_days);
	}
}

function get_parent($child_id) {
    $ci =& get_instance();
    $ci->db->where('module_id', $child_id);
    $result = $ci->db->get('module');

    if ($result->num_rows() > 0) {
        $row = $result->row_array();
        if ($row['parent_id'] != 0) {            
            return get_parent($row['parent_id']);
        } else {
            $id = $row['module_id'];
        }
        
        return $id;
    }
    
    return false;
}

function number_to_words($number) {
   
    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'fourty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        1000000             => 'million',
        1000000000          => 'billion',
        1000000000000       => 'trillion',
        1000000000000000    => 'quadrillion',
        1000000000000000000 => 'quintillion'
    );
   
    if (!is_numeric($number)) {
        return false;
    }
   
    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . number_to_words(abs($number));
    }
   
    $string = $fraction = null;
   
    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }
   
    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= number_to_words($remainder);
            }
            break;
    }
   
    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }
   
    return $string;
}


function ajax_push($command, $data = array(), $channel = 'default') {
		$ci =& get_instance();

		$APEserver = $ci->config->item('ape_server');
		$APEPassword = 'testpasswd';

		$cmd = array(array(
			'cmd' => 'inlinepush',
			'params' => array(
				'password' => $APEPassword,
				'raw' => $command,
				'channel' => $channel,
				'data' => $data
				)
		));
		return true;
		return file_get_contents($APEserver.rawurlencode(json_encode($cmd)));
}

function prepare_filters($module_filters,$all_status = false) {
	$anchors = array();
    foreach($module_filters as $filter):
    	if ($filter['count'] > 0 || $all_status == true):
	        if (!isset($filter['priority'])) {
	            $filter['priority'] = 'high';
	        }

	        switch ($filter['priority']) {
	            case 'high':
	                $bgclass = 'bg-red';
	                break;
	            case 'medium':
	                $bgclass = 'bg-orange';
	                break;
	            case 'low':
	                $bgclass = 'bg-blue';
	                break;                                                        
	            default:
	                # code...
	                break;
	        }
	        $anchors[] = anchor(
	                    $filter['link'],
	                    '<span class="align-left aside-link">'.$filter['text'].'</span>' . '<span class="' . $bgclass . ' ctr-inline align-right">' . $filter['count'] . '</span>'
	                );    
	    endif;
    endforeach;

    return $anchors;
}

function display_date($date_format, $date) {
	if ($date == '0000-00-00' || is_null($date) || $date == '' || $date == '0000-00-00 00:00:00') {
		return '';
	} else {
		return date($date_format, $date);
	}
}

function show_file_dl( $file ){ 
	switch(strtolower($file['extension'])){
		case 'pdf':
			$class = "pdf";
			break;
		case 'doc':
		case 'docx':
			$class = "doc";
			break;
		case 'txt':
			$class = "txt";
			break;
		case 'csv':
		case 'xls':
		case 'xlsx':
			$class = "xl";
			break;
		case 'ppt':	
		case 'pps':
		case 'pptx':
			$class = 'ppt';
			break;
		case 'mp3':	
		case 'wma':
		case 'wav':
			$class = 'music';	
			break;
		case '3gp':	
		case 'avi':
		case 'flv':
		case 'mov':	
		case 'mp4':
		case 'mpg':
		case 'swf':	
		case 'vob':
		case 'wmv':
			$class = 'mov';	
			break;
		case 'bmp':	
		case 'gif':
		case 'jpg':
		case 'png':	
		case 'tif':
			$class = 'pic';	
			break;	
		case '7z':	
		case 'zip':
		case 'rar':
		case 'tar.gz':
			$class = 'zip';
			break;
	} 
	$filename = substr($file['basename'], 15);
	if(empty($file['dbrow']->scribd_apikey )){
		return '<a class="'.$class.'" target="_new" href="'.base_url().$file['server_path'].'">'.$filename.'</a>
		<!--span class="fileSize">'.$file['size'].' KB</span-->';
	}
	else{
		return '<a class="'.$class.'" href="javascript:get_scribd_doc(\''.$file['dbrow']->scribd_doc_id.'\', \''. $file['dbrow']->scribd_access_key .'\')">'.$filename.'</a>
		<!--span class="fileSize">'.$file['size'].' KB</span-->';
	}
}

function datediff($interval, $datefrom, $dateto, $using_timestamps = false)
{
    /*
    $interval can be:
    yyyy - Number of full years
    q - Number of full quarters
    m - Number of full months
    y - Difference between day numbers
    (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
    d - Number of full days
    w - Number of full weekdays
    ww - Number of full weeks
    h - Number of full hours
    n - Number of full minutes
    nn - Number of full minutes --ceil and abs value --ryanp
    s - Number of full seconds (default)
    */
   
    if (!$using_timestamps) {
        $datefrom = strtotime($datefrom, 0);
        $dateto = strtotime($dateto, 0);
    }
    $difference = $dateto - $datefrom; // Difference in seconds
   
    
    switch($interval)
	{
		case 'yyyy': // Number of full years
		 
			$years_difference = floor($difference / 31536000);
			if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom)+$years_difference) > $dateto) {
				$years_difference--;
			}
			if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto)-($years_difference+1)) > $datefrom) {
				$years_difference++;
			}
			$datediff = $years_difference;
		break;
		 
		case "q": // Number of full quarters
		 
			$quarters_difference = floor($difference / 8035200);
			while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($quarters_difference*3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
				$months_difference++;
			}
			$quarters_difference--;
			$datediff = $quarters_difference;
		break;
		 
		case "m": // Number of full months
		 
			$months_difference = floor($difference / 2678400);
			while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
				$months_difference++;
			}
			$months_difference--;
			$datediff = $months_difference;
		break;
		 
		case 'y': // Difference between day numbers
			$datediff = date("z", $dateto) - date("z", $datefrom);
		break;
		 
		case "d": // Number of full days 
			$datediff = floor($difference / 86400);
		break;
		 
		case "w": // Number of full weekdays
		 
			$days_difference = floor($difference / 86400);
			$weeks_difference = floor($days_difference / 7); // Complete weeks
			$first_day = date("w", $datefrom);
			$days_remainder = floor($days_difference % 7);
			$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
			if ($odd_days > 7) { // Sunday
				$days_remainder--;
			}
			if ($odd_days > 6) { // Saturday
				$days_remainder--;
			}
			$datediff = ($weeks_difference * 5) + $days_remainder;
		break; 
		case "ww": // Number of full weeks         
			$datediff = floor($difference / 604800);
		break;
		case "h": // Number of full hours
			$datediff = floor($difference / 600);
		break;
		case "n": // Number of full minutes
			$datediff = floor($difference / 60).'<br>';
			
			/*if($difference > 0){
				$datediff = ceil(abs($difference)/60); //fixed for missing 1min --edit by rynp 12-10-09
			}*/
		break;
		case "nn": // Number of full minutes --ceil and abs
			//fixed for missing 1min in UT --edit by rynp 12-10-09
			$difference2 = $difference+60;
			$datediff = floor($difference2/ 60).'<br>';
			//$datediff = ceil(abs($difference)/60); 
		break;
		default: // Number of full seconds (default)
			$datediff = $difference;
		break;
    }
    
    return $datediff;
}

function is_valid_time($datetime)
{
	return !($datetime == '0000-00-00 00:00:00' || $datetime == '' || is_null($datetime));
}

function hoursToSeconds ($hour) { // $hour must be a string type: "HH:mm:ss"

    $parse = array();
    if (!preg_match ('#^(?<hours>[\d]{2}):(?<mins>[\d]{2}):(?<secs>[\d]{2})$#',$hour,$parse)) {
         // Throw error, exception, etc
         throw new RuntimeException ("Hour Format not valid");
    }

         return (int) $parse['hours'] * 3600 + (int) $parse['mins'] * 60 + (int) $parse['secs'];

}

function acronym( $string = '' ) {
    $words = explode(' ', $string);
    if ( ! $words ) {
        return false;
    }
    $result = '';
    foreach ( $words as $word ) $result .= $word[0];
    return strtoupper( $result );
}	