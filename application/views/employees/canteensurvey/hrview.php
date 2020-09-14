<script>
$(document).ready(function(){
	init_datepick();
});
	
</script>
	<input type="hidden" id="is_hr" name="is_hr" value="0" />
 	<div class="form-item odd">
        <label class="label-desc gray" for="family[birthdate][]">
            Period Date:
        </label>
        <div class="text-input-wrap">
            <input type="text" class="input-text date" style="width:150px" name="date_period_from">
            <span>&nbsp;To&nbsp;</span>
            <input type="text" class="input-text date" name="date_period_to" style="width:150px">
        </div>
    </div>