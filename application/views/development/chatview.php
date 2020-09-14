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
    
		<?php $this->chat->printJavascript(); ?>
    <?php $this->chat->printStyle(); ?>
    <?php $this->chat->printChat();?>
    
    <!-- END MAIN CONTENT -->
</div>