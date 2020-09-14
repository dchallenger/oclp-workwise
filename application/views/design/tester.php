<div id="body-content-wrap" class="">
	<!-- start #page-head -->
    <div id="page-head" class="page-info">
        <div id="page-title">
            <h2 class="page-title"><?='<span class="title">'.$this->listview_title.'</span>';?></h2>	
        </div>	
        <div id="page-desc" class="align-left"><p><?=$this->listview_description?></p></div>						
        <?php 								
			// Page Nav Structure
			if ( isset($pnav) ) {
				echo $pnav;
			}								
        ?>	            
        <div class="clear"></div>
    </div>
    <!-- end #page-head -->
    
    <!-- content alert messages -->
    <div id="message-container">
        <?php 
			if( isset($msg) ){
            	echo is_array($msg) ? implode("\n", $msg) : $msg;
			}
			if(isset($flashdata)){
				echo $flashdata;
			}
		?>
    </div>
    <!-- content alert messages -->
	<div class="spacer"></div>
    
    
    
    <!-- END MAIN CONTENT -->
</div>