
<form id="overtime_export" method='post' action='<?php echo site_url('dtr/employee_overtime_report/export');?>'>
<div class="col-2-form">
    <div class="date_range" style="float:left; width:77%;margin-bottom:10px;">
        <div style="float:left;padding-left:5px">Date: <input type="text" id="date_from" name="date_from" class="date"> to <input type="text" id="date_to" name="date_to" class="date"></div>
    </div>
    <div class="form-submit-btn">
            <div class="icon-label-group">
                <div class="icon-label">
                    <a rel="record-save" class="filter-dtr" onclick="get_data();" href="javascript:void(0);" >
                        <span>Generate List</span>
                    </a>            
                </div>
                <div class="icon-label"><!-- added /-->
                    <a rel="record-save" class="icon-16-export" value='test' onclick="export_file();" href="javascript:void(0);">
                        <span>Export</span>
                    </a>            
                </div>
            </div>
    </div>  
</div>
<table id="jqgridcontainer"></table>
<div id="jqgridpager"></div>
</form>