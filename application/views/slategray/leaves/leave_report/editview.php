	<?php if( isset($error) ) : ?>				
        <div id="message_box" class="attention" style="padding-left: 60px;width:60%;margin: 0 auto;">
            <img src="<?=base_url().$this->userinfo['theme']; ?>/images/exclamation-big.png" alt="" >
            <h3 style="margin: 0.3em 0 0.5em 0">Oops! <?=$error?></h3>

            <p><?=$error2?></p>
        </div>
    <?	else :?>
            <div class="form-submit-btn">
                <div class="icon-label-group">
                    <div class="icon-label">
                        <a rel="record-save" class="icon-16-export" href="javascript:void(0);" onclick="$('#export-form').submit()">
                            <span>Export</span>
                        </a>            
                    </div>
                </div>
            </div>                
            <div class="clear"></div>

            
            <form id="export-form" method="post" action="<?=site_url('leave/leave_report/export')?>">
              <input type="hidden" name="previous_page" id="previous_page" value="<?=base_url().$this->module_link?>/detail"/>        
              <div id="form-div">
                <h3 class="form-head">Leave Report<a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );return false;" class="align-right other-link noborder" href="#">Hide</a></h3>
                <p></p>
                <div class="col-2-form">
                  
                  <div class="form-item odd ">
                    <label class="label-desc gray" for="leave_type">Leave Type:</label>
                    <div class="select-input-wrap">
                        <select id="leave_type" name="leave_type">
                            <option value="">Select…</option>
                            <option value="1">Sick Leave</option>
                            <option value="2">Vacation Leave</option>
                            <option value="3">Emergency Leave</option>
                            <option value="4">Bereavement Leave</option>
                            <option value="5">Maternity Leave</option>
                            <option value="6">Paternity Leave</option>
                            <option value="7">Leave Without Pay</option>
                        </select>
                    </div>
                  </div>
                  <div class="form-item even ">
                    <label class="label-desc gray" for="leave_period">Date Period:</label>
                      <div class="text-input-wrap">
                        <input type="text" name="leave_period_start" id="date_start" style="width:30%;" class="input-text date"/>
                        &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;
                        <input type="text" name="leave_period_end" id="date_end" style="width:30%;" class="input-text date"/>
                      </div>
                  </div>
                  <div class="form-item odd">
                    <label class="label-desc gray" for="leave_status">Leave Status:</label>
                      <div class="select-input-wrap">
                        <select id="leave_status" name="leave_status">
                            <option value="">Select…</option>
                            <option value="3">Approve</option>
                            <option value="4">Disapprove</option>
                            <option value="5">Cancelled</option>
                            <option value="2">For Approval</option>
                        </select>
                      </div>
                  </div>
                  <div class="spacer"></div>
                </div>
                <div class="clear"></div>
                <div class="spacer"></div>
            	</div>    
            </form>
            <?php
                if( isset($views_outside_record_form) && sizeof($views_outside_record_form) > 0 ) :
                    foreach($views_outside_record_form as $view) :
                        $this->load->view($this->userinfo['rtheme'].'/'.$view);
                    endforeach;
                endif;
            ?>    
            <div class="form-submit-btn">
                <div class="icon-label-group">
                    <div class="icon-label">
                        <a rel="record-save" class="icon-16-export" href="javascript:void(0);" onclick="$('#export-form').submit()">
                            <span>Export</span>
                        </a>            
                    </div>
                </div>
            </div>                
            <div class="clear"></div><?php
        endif;
    ?>
    <!-- END MAIN CONTENT -->
</div>