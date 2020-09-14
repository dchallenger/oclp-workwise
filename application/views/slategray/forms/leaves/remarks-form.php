<form name="remarks-form">
    <input name="record_id" value="<?php echo $this->input->post('record_id')?>" type="hidden">
    <div>
        <h3 class="form-head">HR Remarks</h3>
        <p></p>
        <div class="col-2-form">
            <div class="form-item odd ">
                <div class="radio-input-wrap">
                    <input type="radio" class="input-radio" value="1" id="fit_to_work-yes" name="fit_to_work" tabindex="1" <?php if( $this->input->post('fit_to_work') == 1 ){ ?> checked="checked" <?php } ?> >
                    <label class="check-radio-label gray" for="fit_to_work-yes">Valid</label>
                    <input type="radio" class="input-radio" value="0" id="fit_to_work-no" name="fit_to_work" <?php if($this->input->post('fit_to_work') == 0 ){ ?> checked="checked" <?php } ?> >
                    <label class="check-radio-label gray" for="fit_to_work-no">Invalid</label>
                </div> 
            </div>
            <div class="form-item even ">
                <label class="label-desc gray" for="remarks">
                Remarks:
                </label>
                <div class="textarea-input-wrap">
                    <textarea class="input-textarea" id="remarks" name="remarks" tabindex="2" rows="5"><?php if($this->input->post('remarks')){ echo $this->input->post('remarks'); } ?></textarea>
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