<form name="remarks-form">
    <input name="record_id" value="<?php echo $this->input->post('record_id')?>" type="hidden">
    <div>
        <h3 class="form-head">HR Remarks</h3>
        <p></p>
        <div class="col-1-form">
            <div class="form-item odd ">
                <label class="label-desc gray" for="remarks">
                Remarks:
                </label>
                <div class="textarea-input-wrap">
                    <textarea class="input-textarea" id="remarks" name="remarks" tabindex="2" rows="5" style="width:100%"><?php if($this->input->post('remarks')){ echo $this->input->post('remarks'); } ?></textarea>
                </div>
            </div>
        </div>
        <div class="spacer"></div>
    </div>
    <div class="form-submit-btn">
        <div class="icon-label-group">
            <div class="icon-label">
                <a onclick="save_remarks()" href="javascript:void(0);" class="icon-16-disk">
                    <span>Save Remarks</span>
                </a>            
            </div>
        </div>
        <div class="or-cancel">
            <span class="or">or</span>      <a href="javascript:void(0)" onclick="Boxy.get(this).hide()" class="cancel">Cancel</a>
        </div>
    </div>
</form>

<script>
    function save_remarks(){
        if($('form[name="remarks-form"] textarea').val() != ""){
            $.ajax({
              url: module.get_value('base_url') + module.get_value('module_link') + '/save_remarks',
              type: 'post',
              data: $('form[name="remarks-form"]').serialize(),
              beforeSend: function(){
                $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Saving, please wait...</div>' });      
              },
              success: function (data) {
                goto_detail( data );
              }
            });
        }
        else{
            message_growl('error', 'Please enter your remarks.');
        }
    }
</script>