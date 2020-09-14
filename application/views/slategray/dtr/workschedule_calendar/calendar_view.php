<script type="text/javascript" >

var p_calendar = function() { };

var _set_c = p_calendar.prototype;

_set_c.is_above_current = function(start, end) { 

	var cur_date = new Date();
	var start_date = new Date(start);
	start_date.setHours(start_date.getHours() + 24);
	
	var end_date = new Date(end);
	end_date.setHours(end_date.getHours() + 24);

	if(cur_date > start_date || cur_date > end_date) 
		return true;
	else
		return false;
};

_set_c.sql_format_date = function(start, end, prev_start, prev_end) {
	var header = { 'start' : start,
				   'end' : end,
				   'prev_start' : prev_start,
				   'prev_end' : prev_end
				   };
				   
	var q = new Array;

	for(var key in header)
	{
		if(header[key])
			q[key] = this.format_date(header[key]);
	}

	return q;
};

_set_c.format_date = function(unformatted_date = false) {

	if(unformatted_date)
		var var_date = new Date(unformatted_date);
	else 
		var var_date = new Date();

	var d = var_date.getDate();
	var m = var_date.getMonth()+1;
	var y = var_date.getFullYear();

	if(d < 10) 
		d = '0'+d;
	if(m < 10) 
		m = '0'+m;

	return y+'-'+m+'-'+d;

};

_set_c.is_overlapping = function(date_emp_added = false, start = false, end = false) {

	if(date_emp_added && start && end)
	{
		var ctr = 0;
		var finals = [];

		if(date_emp_added != "")
		{
			var date = date_emp_added.split(',');
			for(var i in date) {
				if($.trim(date[i]) != "")
				{
					said = date[i].split('=');
					final = said[0].split('/');
					for(var f in final)
						finals.push(final[f]);
				}
			}
		}

		if(finals.length != 0)
		{
			while(ctr<finals.length)
			{
				var formatted_dates = this.sql_format_date(start, end);
				
				if((finals[ctr] <= formatted_dates['start'] && formatted_dates['start'] <= finals[ctr+1]) || (finals[ctr] <= formatted_dates['end'] && formatted_dates['end'] <= finals[ctr+1]) || (formatted_dates['start'] <= finals[ctr] && finals[ctr+1] <= formatted_dates['end']))
					return true; 

				ctr=ctr+2;
			}
		}
	}

}

_set_c.curdate = function() {

	var tdy = new Date();
	var d = tdy.getDate();
	var m = tdy.getMonth()+1; 
	var y = tdy.getFullYear();

	return y+'-'+m+'-'+d;
};

</script>

<div class="form-item odd">
	<div id="multi-select-loader"> </div>              
	<div id="multi-select-main-container" style="display:none">
	    <label class="label-desc gray" for="department" id="category_selected"> </label>

	    <div class="multiselect-input-wrap" id="multi-select-container">

	    </div>

	</div>

</div>

<br /> <br /> <br />
  <br /> <br /> <br />
<br /> <br /> <br />
  <br /> <br />

<div id="calendar"> </div>

<input type="textbox" name="shift_list" id="shift_list" style="display:none"/>

<input type="textbox" name="date_emp_deleted" id="date_emp_deleted" style="display:none"/>

<input type="textbox" name="date_emp_added" id="date_emp_added" style="display:none"/>