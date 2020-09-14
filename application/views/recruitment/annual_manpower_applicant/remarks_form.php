<form name="remarks-form" id="remarks-form" enctype="multipart/form-data" method="post">
<div>
	<label>Remarks<span class="red">*</span><label>
	<div class="input-wrap"><textarea name="remarks" id="remarks" class="input-text"></textarea></div>
</div>
</form>

<div class="form-submit-btn">
    <div class="icon-label-group">

        <div class="icon-label">
            <a rel="record-save" class="icon-16-disk" onclick="submit_remarks_status()" join="0" href="javascript:void(0);">
                <span><span>Send</span></span>
            </a>            
        </div>

    </div>
</div> 

<script type="text/javascript">
	$(document).ready(function() {
		
		
	});

	function submit_remarks_status(){

		var remarks = $('#remarks').val();
		var record_id = '<?=$_POST['record_id'];?>';
		if( remarks != '' ){
			change_status(record_id,4,true,remarks);
		}
		else{
			//$.unblockUI();	
			//Boxy.get($('#boxyhtml')).hide();
			message_growl('error', 'Remarks field is required');
		}
	}

</script>
