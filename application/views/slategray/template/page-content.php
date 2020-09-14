<div id="page-wrap">
	<div class="clear"></div>
	<div class="content-wrap">
  		<?php $this->load->view( 'template/sidebar' ); ?>
  		<div id="body-content-wrap">
  		<a href="javascript:;" id="btn-panel" class="close-panel"></a>
	  		<?php $this->load->view( 'template/page-header' ); ?>  		
	    	<?php $this->load->view( $content ); ?>
      </div>
      <div class="spacer">&nbsp;</div>
	</div>
</div>