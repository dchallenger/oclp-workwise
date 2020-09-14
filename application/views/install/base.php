<!DOCTYPE html>
<html lang="en">
  <head>
	    <meta charset="utf-8">
		<!-- Twitter bootstrap, temporary lang -->
		<link rel="stylesheet" href="http://twitter.github.com/bootstrap/1.4.0/bootstrap.min.css">
		<title>HRIS - Install</title>
		<style>
			.container { 
				background-color: #DCEAF4; 
				padding: 10px;
				margin-top: 50px;
				width: 640px;
			}

			#content { 
				background: url(<?php echo site_url('themes/blue/images/hdi-swirls.png')?>) no-repeat fixed -500px -350px white; width:100%; height:100%; position:	absolute; font:12px Arial, Helvetica, sans-serif; padding:0px 0px 0px 0px; }
				
			.error { color: red; }			
		</style>
		<!--<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>-->
		<script type="text/javascript" src="<?=base_url();?>lib/jquery/jquery-1.6.2.min.js"></script>
        <script type="text/javascript" src="<?=base_url();?>lib/jquery/jquery-ui-1.8.14.custom.min.js"></script>
        <script type="text/javascript" src="<?=base_url();?>lib/jquery/jquery.validate.min.js"></script>
	</head>
	<body>
		<div id="content">		
		<?php $this->load->view('install/' . $content);?>
		</div>
	</body>
</html>