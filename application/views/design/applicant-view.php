<!-- start #page-head -->
<div id="page-head" class="page-info">
    <div id="page-title">
        <h2 class="page-title"><span class="title"><?=$this->detailview_title;?></span></h2>    
    </div>  
    <div id="page-desc" class="align-left"><p><?=$this->detailview_description?></p></div>                        
    <?php                               
        // Page Nav Structure
        if ( isset($pnav) ) echo $pnav;                         
    ?>              
    <div class="clear"></div>
</div><!-- end #page-head -->
<?php $this->load->view( $this->userinfo['rtheme'].'/template/sidebar' ); ?>
<div id="body-content-wrap" class=""> 
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
  
  <dl id="core-elements">
  
  
    <dt>Class name: icon-label-group</dt>
    <dd>
    		
            <div class='icon-label-group'>
                    <div class='icon-label'><a class='icon-16-add icon-16-add-listview' href='javascript:void(0)'>
                        <span>Add</span></a>
                    </div>
                    <div class='icon-label'><a class='icon-16-delete delete-array' href='javascript:void(0)'>
                        <span>Delete</span></a>
                    </div>
  </div>
          
          <div class='icon-label-group'>
            <div class='icon-label'><a class='icon-16-add icon-16-add-listview' href='javascript:void(0)'>
                <span>Add</span></a>
            </div>
            <div class='icon-label'><a class='icon-16-delete delete-array' href='javascript:void(0)'>
            	<span>Delete</span></a>
            </div>
          </div>
    </dd>
    
    <dt>Class name: icon-group</dt>
    <dd>
          <span class="icon-group">
          	<a href="javascript:void(0)" tooltip="View" class="icon-button icon-16-info" original-title="View"></a>
            <a href="javascript:void(0)" tooltip="View Location" class="icon-button icon-16-map-pin" original-title="View Location"></a>
            <a href="javascript:void(0)" tooltip="Add Comment" class="icon-button icon-16-balloon-ellipsis" original-title="Add Comment"></a>
            <a href="javascript:void(0)" tooltip="Edit" class="icon-button icon-16-edit" original-title="Edit"></a>
            <a href="javascript:void(0)" tooltip="Delete" class="icon-button icon-16-delete delete-disabled" original-title="Delete"></a></span>
    </dd>
    
    <dt>Class name: icon-group > icon-button</dt>
   	<dd> <span class="icon-group">
		<a href="javascript:void(0);" class="icon-button icon-16-add"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-minus"></a>
	</span>
    </dd>
    
    <dt>Icon Listing with class names (16px)</dt>
   	<dd> <span class="icon-group">
    		<a href="javascript:void(0);" class="icon-button icon-default-16" toolTip="icon-default-16"></a>
				<a href="javascript:void(0);" class="icon-button icon-16-add" toolTip="icon-16-add"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-minus" toolTip="icon-16-minus"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-delete" toolTip="icon-16-delete"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-refresh" toolTip="icon-16-refresh"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-active" toolTip="icon-16-active"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-xgreen-orb" toolTip="icon-16-xgreen-orb"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-disk" toolTip="icon-16-disk"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-disk-back" toolTip="icon-16-disk-back"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-listback" toolTip="icon-16-listback"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-fmlink" toolTip="icon-16-fmlink"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-edit" toolTip="icon-16-edit"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-picklist" toolTip="icon-16-picklist"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-info" toolTip="icon-16-info"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-map-pin" toolTip="icon-16-map-pin"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-balloon-ellipsis" toolTip="icon-16-balloon-ellipsis"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-chart-up" toolTip="icon-16-chart-up"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-chart" toolTip="icon-16-chart"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-joborder-status" toolTip="icon-16-joborder-status"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-calendar-month" toolTip="icon-16-calendar-month"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-user-business" toolTip="icon-16-user-business"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-alert-status" toolTip="icon-16-alert-status"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-ribbon" toolTip="icon-16-ribbon"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-doc-text" toolTip="icon-16-doc-text"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-document-view" toolTip="icon-16-document-view"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-transform" toolTip="icon-16-transform"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-split" toolTip="icon-16-split"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-search-opts" toolTip="icon-16-search-opts"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-chart-up" toolTip="icon-16-chart-up"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-chart" toolTip="icon-16-chart"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-joborder-status" toolTip="icon-16-joborder-status"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-calendar-month" toolTip="icon-16-calendar-month"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-alert-status" toolTip="icon-16-alert-status"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-ribbon" toolTip="icon-16-ribbon"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-doc-text" toolTip="icon-16-doc-text"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-document-view" toolTip="icon-16-document-view"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-transform" toolTip="icon-16-transform"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-split" toolTip="icon-16-split"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-xrefresh" toolTip="icon-16-xrefresh"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-portlet-fold" toolTip="icon-16-portlet-fold"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-portlet-unfold" toolTip="icon-16-portlet-unfold"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-application-small" toolTip="icon-16-application-small"></a>
        <a href="javascript:void(0);" class="icon-button icon-16-key" toolTip="icon-16-key"></a>
	</span>
    </dd>
    
    <dt>Navigator buttons</dt>
    <dd>
    		<div class="page-navigator">
        		<div class="btn-prev-disabled"><a href="javascript:void(0)"><span>Previous</span></a></div>
        		<div class="btn-prev"><a href="javascript:void(0)"><span>Previous</span></a></div>
            <div class="btn-next"><a href="javascript:void(0)"><span>Next</span></a></div>
            <div class="btn-next-disabled"><a href="javascript:void(0)"><span>Next</span></a></div>
        </div>
    </dd>
    
    <dt>Navigator buttons 2</dt>
    <dd>
    		<div class="align-left"><a href="index.html" class="gray-btn"> <span class="gray-btn-inner">  <span class="gray-icon"></span> <span class="gray-btn-text">Previous</span></span> </a></div>
        
        <div class="align-left"><a href="index.html" class="gray-btn"> <span class="gray-btn-inner"> <span class="gray-btn-text">Next</span> <span class="gray-icon-next"></span> </span> </a></div>
    </dd>
    
    
  </dl>
 
  
</div> <!-- #core elements --> 

<!-- END MAIN CONTENT -->
</dl>
