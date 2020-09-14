
 <div class="form-multiple-add" style="display: block;">
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail" rel="session">DELETE</a>
                <input type="hidden" class="session_rand" name="session[session_rand][]" value="<?= $session_rand ?>" />
            </span>
        </div>
    </h3>
    <div class="form-item odd ">
        <label class="label-desc gray" for="date">Session No.:</label>
        <div class="text-input-wrap">    
            <input type="text" readonly="" class="input-text session_no" style="width:100px;" value="<?php echo $session_count; ?>" name="session[session_no][]">           
        </div>
    </div>
    <div class="form-item even ">
        <label class="label-desc gray" for="planned_date">Training Date:<span class="red font-large">*</span></label>
        <div class="text-input-wrap">				
			<input type="text" readonly="" class="datepicker input-text datepicker session_date" value="" name="session[session_date][]">
		</div>                                    
	</div>
    <div class="form-item even ">
        <label class="label-desc gray" for="date">Session Time:</label>
        <div class="text-input-wrap">               
            <input type="text" readonly="" class="timepicker input-text sessiontime_from" value="9:00 am" name="session[sessiontime_from][]">
             to 
            <input type="text" readonly="" class="timepicker input-text sessiontime_to" value="6:00 pm" name="session[sessiontime_to][]">
        </div>                                    
    </div>
    <div class="form-item even ">
        <label class="label-desc gray" for="date">Breaktime:</label>
        <div class="text-input-wrap">               
            <input type="text" readonly="" class="timepicker input-text breaktime_from" value="" name="session[breaktime_from][]">
             to 
            <input type="text" readonly="" class="timepicker input-text breaktime_to" value="" name="session[breaktime_to][]">
        </div>                                    
    </div>
    <div class="clear"></div>
</div>
<div class="clear"></div>  
            