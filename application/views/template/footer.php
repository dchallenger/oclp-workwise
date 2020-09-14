	<?php
    if($this->module != 'login') : ?>
        
        
        <div id="page-footer">
        		<style>
						<?php
            	if($footer_widget_state == "collapse"){ ?>
								.pf-expand{
									margin-top: 100px;
								}
								
								.pf-collapse{
									margin-top: -140px;
								}
							<?php } ?>
						</style>
            <div class="pf-wrap">
            	<div class="pf-expand" <?php ?>>
        			<div class="hide-footer"><a href="javascript:void(0);" class="tipsy-autons" ></a></div>
        			
                <ul>
                		<li class="quicklinks"><a href="javascript:void(0);" class="tipsy-autons" tooltip="Policies and Procedures"><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-footer-manual16.png" /></a></li>
                    <li class="quicklinks"><a href="javascript:void(0);" class="tipsy-autons" tooltip="Employee Directory"><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-footer-directory16.png" /></a></li>
                    <li class="quicklinks"><a href="javascript:void(0);" class="tipsy-autons" tooltip="Organizational Chart"><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-footer-org16.png" /></a></li>
                   	<li><a href="javascript:void(0);" title="Who's online" ><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-online.png"/>Who's Online</a></li>
                   	<li><a href="javascript:void(0);" title="Who's online" ><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-suggestionbox.png"/>Suggestion Box</a></li>
                </ul>
              
        			<div class="footer-copyright"><span><?php echo $meta['footer']?> Powered by:</span> <img src="<?php echo base_url() . $this->userinfo['theme']?>/images/footer-logo.png"/></div>
        		</div>
        		<div class="pf-collapse">
                	<ul>
                  	<li><a href="javascript:void(0);" class="tipsy-autons"><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-footer-manual16.png"/></a></li>
                    <li><a href="javascript:void(0);" class="tipsy-autons" ><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-footer-directory16.png"/></a></li>
                    <li><a href="javascript:void(0);" class="tipsy-autons" ><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-footer-org16.png"/></a></li>
                    <li><a href="javascript:void(0);" class="tipsy-autons" ><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-online.png"/></a></li>
                    <li><a href="javascript:void(0);" class="tipsy-autons" tooltip="Suggestion Box"><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-suggestionbox.png"/></a></li>
        			</ul>
        			<div class="show-footer">
            			<a href="javascript:void(0);"  class="tipsy-autons"></a>
            		</div>
        		</div>
        	</div>
        </div>
    <?php
    endif;
    ?>
    </body>
</html>              