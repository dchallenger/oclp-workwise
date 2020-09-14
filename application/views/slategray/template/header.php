<?php
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );
?>
<!doctype html>
<!--[if lt IE 7 ]> <html class="ie ie6 no-js" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="ie ie7 no-js" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="ie ie8 no-js" lang="en"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie ie9 no-js" lang="en"> <![endif]-->
<!--[if gt IE 9]><!--><html class="no-js" lang="en"><!--<![endif]-->
<head>
        <meta charset="utf-8">
        <title><?=$meta['title'];?> &copy; - <?=$this->module_name ?></title>
        <meta name="author" content="<?=$meta['author'];?>" />
        <meta name="keywords" content="<?=$meta['keywords'];?>" />
        <meta name="description" content="<?=$meta['description'];?>" />
        <meta name="copyright" content="<?=$meta['copyright'];?>" />
        <link href="<?=css_path('favicon.ico');?>" rel="shortcut icon">
        
        <!-- Load Theme Style -->
        <link rel="stylesheet" type="text/css" href="<?php echo css_path('styles.css')?>" />
       
        <script type="text/javascript" src="<?=base_url();?>lib/modernizr/html5-3.6-respond-1.1.0.min.js"></script>
		<script type="text/javascript" src="<?=base_url();?>lib/modernizr/modernizr-1.7.min.js"></script>

        
        <!-- Set JS Global Variables -->
        <?php
            $jsdata['userinfo'] = $this->userinfo;
            if( $this->module != 'login' ) $jsdata['user_access'] = $this->user_access[$this->module_id];
            $jsdata['module_id'] = $this->module_id;
            $jsdata['module'] = $this->module;
            $jsdata['module_link'] = $this->module_link;
            $jsdata['idle_time'] = $this->config->item('idle_time');
            $jsdata['base_url'] = base_url();
            $jsdata['fcpath'] = FCPATH;
            $jsdata['method'] = $this->method;
            $jsdata['record_id'] = $this->input->post( 'record_id' ) ? $this->input->post( 'record_id' ) : '-1';
            $jsdata['client_no'] = $this->config->item('client_no');
            $js_data = base64_encode( ( serialize($jsdata) ) );
        ?>
        <script type="text/javascript" src="<?=base_url();?>lib/user_module.js.php?data=<?php echo $js_data?>"></script>
		
        <!-- Load jQuery and jQuery UI -->
        <script type="text/javascript" src="<?=base_url();?>lib/jquery/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="<?=base_url();?>lib/jquery/jquery-ui-1.8.14.custom.min.js"></script>
        <script type="text/javascript" src="<?=base_url();?>lib/jquery/jquery.datepick.timepicker.addon.js"></script>
        <script type="text/javascript" src="<?=base_url();?>lib/jquery/jquery.blockUI.js"></script>

		<script type="text/javascript" src="<?=base_url();?>lib/js/jquery.idle-timer.js"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo base_url()?>lib/jquery/css/smoothness/jquery-ui-1.8.15.custom.css" />

        <!-- SuperFish -->
        <script type="text/javascript" src="<?=base_url();?>lib/superfish/superfish.js"></script>
        <script type="text/javascript" src="<?=base_url();?>lib/superfish/hoverIntent.js"></script>
        <link rel="stylesheet" type="text/css" href="<?=base_url();?>lib/superfish/superfish.css" media="screen" />

        <!-- FB Style Chat -->
	    <?php if ($this->module != "login" && $this->config->item('allow_chat_on_footer')) : ?>
          <script type="text/javascript" src="<?=base_url();?>lib/chat/js/chat.js"></script>
          <script type="text/javascript" src="<?=base_url();?>lib/chat/js/quicksearch.js"></script>
          <link type="text/css" rel="stylesheet" media="all" href="<?=base_url();?>lib/chat/css/chat.css" />
          <link type="text/css" rel="stylesheet" media="all" href="<?=base_url();?>lib/chat/css/screen.css" />
          <!--[if lte IE 7]>
          <link type="text/css" rel="stylesheet" media="all" href="<?=base_url();?>lib/chat/css/screen_ie.css" />
          <![endif]-->
        <?php endif;?>
        
        <!-- HDI Js -->
        <script type="text/javascript" src="<?=base_url();?>lib/js/hdi.js"></script>
        <script type="text/javascript" src="<?=base_url() ?>lib/js/dynamic.js"></script>
        <script type="text/javascript" src="<?=base_url() ?>lib/js/validate.js"></script>

        <!-- Boxy -->
        <script type="text/javascript" src="<?=base_url();?>lib/boxy0.1.4/javascripts/jquery.boxy.js"></script>
        <link rel="stylesheet" type="text/css" href="<?=base_url();?>lib/boxy0.1.4/stylesheets/boxy.css" media="screen" />

        <!-- Tipsy -->
        <script type="text/javascript" src="<?=base_url(); ?>lib/tipsy1.0.0a/javascripts/jquery.tipsy.js"></script>
        <link type="text/css" rel="stylesheet" href="<?=base_url(); ?>lib/tipsy1.0.0a/stylesheets/tipsy.css" />

        <!-- Qtip -->
		<script type="text/javascript" src="<?=base_url(); ?>lib/qtip/jquery.qtip-1.0.0-rc3.min.js"></script>

        <!-- Load other scripts and styles -->
        <?php  if( isset( $scripts ) ) echo is_array( $scripts ) ? implode( "\n", $scripts ) : $scripts; ?>

        <!-- Load module js --><?php
        $filepath = ($this->parent_path != "" ? $this->parent_path.'/' : '') . $this->module .'.js';
        if(file_exists(FCPATH . "lib/modules/client/". CLIENT_DIR ."/".$filepath)){ ?>
            <script type="text/javascript" src="<?=base_url()?>lib/modules/client/<?php echo CLIENT_DIR?>/<?php echo $filepath?>"></script> <?php
        }
        else{ ?>
            <script type="text/javascript" src="<?=base_url()?>lib/modules/<?php echo $filepath?>"></script><?php
        } ?>
        <!-- Load theme js -->
    	<script type="text/javascript" src="<?=base_url() . $this->userinfo['theme']?>/theme.js"></script>

        <!-- Load APE Client -->
        <!--script type="text/javaScript" src="<?=base_url()?>ape/Build/uncompressed/apeClientJS.js"></script>
        <script type="text/javaScript" src="<?=base_url()?>lib/js/push.js"></script-->
    </head>

    <body>