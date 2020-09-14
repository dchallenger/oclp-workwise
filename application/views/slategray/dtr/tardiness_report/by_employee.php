
<form id="frmtardiness" method='post' action='<?php echo site_url('dtr/employee_tardiness_report/export');?>'>
<input type="hidden" name="employee_id_multiple" id="employee_id_multiple">
<div class="col-2-form">
    <div class="form-item odd ">
        <label class="label-desc gray" for="department">Category:</label>
        <div class="multiselect-input-wrap">
            <select id="category" style="width:400px;" name="category">
                <option value="0">Select</option>
                <?php
                    $result = $this->db->get('report_filtering');
                    if ($result && $result->num_rows() > 0){
                        foreach ($result->result() as $row) {
                ?>
                            <option value="<?php echo $row->report_filtering_id ?>" data-alias="<?php echo $row->report_filtering_alias ?>" data-aliasid="<?php echo $row->report_base_table ?>"><?php echo $row->report_filtering ?></option>
                <?php
                        }
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="form-item even ">
        <label class="label-desc gray" for="department">Date Period:</label>    
        <div style="float:left;padding-left:5px">Date: <input type="text" id="date_from" name="date_from" class="date"> to <input type="text" id="date_to" name="date_to" class="date"></div>
    </div>
    <div class="form-item odd">
        <div id="multi-select-loader"></div>
        <div id="multi-select-main-container" style="display:none">
            <label class="label-desc gray" for="department" id="category_selected"></label>
            <div class="multiselect-input-wrap" id="multi-select-container">
            </div>
        </div>
    </div>
    <div class="form-item odd">
        <div id="multi-select-loader2"></div>
        <div id="multi-select-main-container2" style="display:none">
            <label class="label-desc gray" for="employees" id="category_selected2"></label>
            <div class="multiselect-input-wrap" id="multi-select-container2">
            </div>
        </div>
    </div>    
<!--     <div class="form-item odd">
        <div id="employment_status_container" style="display:none">
            <label class="label-desc gray" for="employment_status">Employment Status</label>
            <div class="multiselect-input-wrap" id="multi-select-container">
                <select id="employment_status" multiple="multiple" class="multi-select" style="width:400px;" name="employment_status[]">
                    <?php 
                        $this->db->where('deleted',0);
                        $employment_status = $this->db->get('employment_status')->result_array();                                
                        foreach($employment_status as $employment_status_record){
                            print '<option value="'.$employment_status_record["employment_status_id"].'">'.$employment_status_record["employment_status"].'</option>';
                        }                   
                    ?>
                </select>
            </div>
        </div>
    </div>   -->      
<!--     <div class="form-item odd">
        <div id="employee_type_container" style="display:none">
            <label class="label-desc gray" for="employee_type">Employment Type</label>
            <div class="multiselect-input-wrap" id="multi-select-container">
                <select id="employee_type" multiple="multiple" class="multi-select" style="width:400px;" name="employee_type[]">
                    <?php 
                        $this->db->where('deleted',0);
                        $employee_type = $this->db->get('employee_type')->result_array();                                
                        foreach($employee_type as $employee_type_record){
                            print '<option value="'.$employee_type_record["employee_type_id"].'">'.$employee_type_record["employee_type"].'</option>';
                        }                   
                    ?>
                </select>
            </div>
        </div>
    </div>   -->                                     
    <div class="spacer"></div>      
    <br clear="all"/>
    <!-- <div style="float:left;padding-left:5px"><button id="filter-dtr" onclick="get_data();">Filter</button></div> -->
    <div class="form-submit-btn">
            <div class="icon-label-group">
                <div class="icon-label">
                    <a rel="record-save" class="filter-dtr" onclick="get_data();" href="javascript:void(0);" >
                        <span>Generate List</span>
                    </a>            
                </div>
                <div class="icon-label"><!-- added /-->
                    <a rel="record-save" class="icon-16-export" value='test' onclick="export_file();" href="javascript:void(0);">
                        <input type='hidden' name='selected_employee' id='test2' value=''/>
                        <input type='hidden' name='date_from2' id='date_from2' value=''/>
                        <input type='hidden' name='date_to2' id='date_to2' value=''/>
                        <span>Export</span>
                    </a>            
                </div>
            </div>
    </div>  
</div>
<table id="jqgridcontainer"></table>
<div id="jqgridpager"></div>
</form>