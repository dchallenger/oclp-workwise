<input type="hidden" name="calendar_id" id="calendar_id" value="<?= $calendar_id ?>" />
<input type="hidden" name="employee_direct" id="employee_direct" value="<?= $employee_direct ?>" />
<table id="module-competencies" style="width:100%;" class="default-table boxtype">
    <tbody>
	
    </tbody>
</table>
<br />
<div >
    <fieldset>
        <div class="form-item odd">
            <label class="label-desc gray" for="date">Total Score:</label>
            <div class="text-input-wrap">               
                <input type="text" class="input-text total_score" style="width:20%;" value="<?= $total_score ?>" readonly="" name="total_score">
            </div>                                    
        </div>
        <div class="form-item odd">
            <label class="label-desc gray" for="date">Average:</label>
            <div class="text-input-wrap">               
                <input type="text" class="input-text average" style="width:20%;" value="<?= $average_score ?>" readonly="" name="average_score">
            </div>                                    
        </div>
    </fieldset>
</div>
