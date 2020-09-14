<h4 class="portlet-head portlet-handle"> 
	<span class="icon <?=empty($portlet_class)?'icon-default-16':$portlet_class ?>" style="margin-top:5px;"></span>
	<span><?=$portlet_name?></span>
</h4>
<div class="portlet-controls"> 
	<!-- a href="javascript:void(0)" tooltip="Close" class="align-right icon icon-16-closeportlet" onclick="close_portlet( <?=$portlet_id?>, '<?=$portlet_file?>' );"></a>
    <span style="border-left:1px solid #eee;float:right">&nbsp;</span -->
  	<a href="javascript:void(0)" tooltip="Refresh" class="align-right icon icon-16-xrefresh" onclick="refresh_portlet( <?=$portlet_id?>, '<?=$portlet_file?>' );"></a> 
  	<a href="javascript:void(0)" tooltip="Collapse/Expand" class="align-right fold-portlet icon <?=($isFolded)?'icon-16-portlet-unfold':'icon-16-portlet-fold'?>" onclick="fold_portlet( $(this), '<?=$portlet_id?>' );"></a>
  	<a href="javascript:void(0)" tooltip="Resize" class="align-right icon icon-16-transform	" onclick="resize_portlet( <?=$portlet_id?>, '<?=$portlet_file?>' );"></a>
    <span style="border-left:1px solid #eee;float:right">&nbsp;</span>
</div>
<div id="portlet-inside-<?=$portlet_id?>" reference="<?=$portlet_file?>" class="portlet-inside" style="display:<?=($isFolded)?'none':'block'?>;" > 
  <img src="<?php echo base_url() . $this->userinfo['theme']?>/images/loading3.gif ?>" height="25px" alt="Loading..." />   
</div>
