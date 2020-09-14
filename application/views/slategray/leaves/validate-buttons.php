<div class="form-submit-btn">
	<?php $show_or = false; ?>

  <div class="icon-label-group">
    <?php if($this->user_access[$this->module_id]['post'] == 1 || $this->user_access[$this->module_id]['hr_health'] == 1):
      $show_or = true;?>
          <div class="icon-label">
              <a class="icon-16-comments" href="javascript:void(0);" onclick="post_remarks(<?php echo $this->key_field_val?>)">
                  <span>Remarks</span>
              </a>            
          </div>
          <div class="icon-label">
              <a class="icon-16-notify" href="javascript:void(0);" onclick="sent_to_approver(<?php echo $this->key_field_val?>)">
                  <span>Send To Approver</span>
              </a>            
          </div>
    <?php endif; ?>
  </div>
  <div class="or-cancel">
      <?php if($show_or) :?><span class="or">or</span><?php endif; ?>
      <a class="cancel" href="javascript:void(0)" rel="action-back">Go Back</a>
  </div>
</div>

<script>
  var remarks_boxy = false;
  function post_remarks( record_id ){
    $.ajax({
      url: module.get_value('base_url') + module.get_value('module_link') + '/get_remarks_form',
      type: 'post',
      data: 'record_id='+record_id,
      beforeSend: function(){
        $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });      
      },
      success: function (data) {
        if(!remarks_boxy){
          remarks_boxy = new Boxy('<div id="remarks_boxy" style="width: 677px;">'+ data.form +'</div>',
          {
            title: 'Remarks',
            draggable: false,
            modal: true,
            center: true,
            unloadOnHide: true,
            show: true,
            afterShow: function(){ $.unblockUI(); },
            beforeUnload: function(){ $.unblockUI(); remarks_boxy = false; }
          });
          boxyHeight(remarks_boxy, '#remarks_boxy');
        }
      }
    });
  }
  
  function sent_to_approver( record_id ){
    $.ajax({
      url: module.get_value('base_url') + module.get_value('module_link') + '/sent_to_approver',
      type: 'post',
      data: 'record_id='+record_id,
      beforeSend: function(){
        $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });      
      },
      success: function (data) {
        go_to_previous_page( data.msg );
      }
    });
  }
</script>