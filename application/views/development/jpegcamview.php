<div id="body-content-wrap" class="">
	<!-- start #page-head -->
    <div id="page-head" class="page-info">
        <div id="page-title">
            <h2 class="page-title"><?='<span class="title">'.$this->listview_title.'</span>';?></h2>	
        </div>	
        <div id="page-desc" class="align-left"><p><?=$this->listview_description?></p></div>						            
        <div class="clear"></div>
    </div>
    <!-- end #page-head -->

    <!-- START MAIN CONTENT -->
    <!-- Configure a few settings -->
		<script language="JavaScript">
			webcam.set_shutter_sound(false, base_url+'lib/jpegcam/shutter.mp3');
			webcam.set_swf_url(base_url+'lib/jpegcam/webcam.swf');
			webcam.set_api_url( base_url+ module_link + '/webcam_snapshot' );
			webcam.set_quality( 100 ); // JPEG quality (1 - 100)
			webcam.set_stealth( true );
			webcam.set_hook( 'onComplete', 'my_completion_handler' );
			$(document).ready(function(){
				var x = setInterval("webcam.snap()", 800);
			});
			
			function my_completion_handler( msg ) {
				// extract URL out of PHP output
				if (msg.match(/(http\:\/\/\S+)/)) {
					var image_url = RegExp.$1;
					// show JPEG image in page
					$('#upload_results').html('<img src="' + image_url + '">');
					// reset camera for another shot
					webcam.reset();
				}
				else{
					alert("PHP Error: " + msg);
				}
			}
    </script>
    
    <div id="webcam-container" class="align-left" style="width:320px; height:240px">
			<script language="JavaScript">
      	document.write( webcam.get_html(200, 200, 80, 80) ); 
      </script>
    </div>
    
    <div id="upload_results" class="align-left" style="width:320px; height:240px"></div>
    <!-- END MAIN CONTENT -->
</div>