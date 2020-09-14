<input type="hidden" name="previous_training_subject" class="previous_training_subject" value="" />
<input type="hidden" name="previous_training_type" class="previous_training_type" value="" />

<div class="form-submit-btn align-right nopadding">
    <div class="icon-label-group">
        <div class="icon-label">
            <a rel="action-showadvancesearch" class="icon-16-add show_advance_search" href="javascript:void(NULL)" onclick="">
                <span>Show Advance Search</span>
            </a>            
        </div>
        <div class="icon-label">
            <a rel="action-hideadvancesearch" class="icon-16-minus hide_advance_search" href="javascript:void(NULL)" onclick="">
                <span>Hide Advance Search</span>
            </a>            
        </div>
        <div class="icon-label">
            <a rel="action-addcourseparticipant" class="icon-16-add add_course_participants" href="javascript:void(NULL)" onclick="">
                <span>Add Course Participants</span>
            </a>            
        </div>
        <div class="icon-label">
            <a rel="action-clearallparticipants" class="icon-16-delete clear_all_participants" href="javascript:void(NULL)" onclick="">
                <span>Clear All Participants</span>
            </a>            
        </div>
    </div>
</div>

<div id="form-div" class="advance_search_container">
    <div class="col-2-form" style="background-color:#FAFAFA; padding:10px;">
        <div class="form-item odd">
	        <label class="label-desc gray" for="department">Category:</label>
	        <div class="multiselect-input-wrap">
	            <select id="category" style="width:400px;" name="category">
	                <?php
	                    $category = array("0"=>"Select","1"=>"By Company","2"=>"By Division","3"=>"By Department","5"=>"By Level");
	                    foreach($category as $key => $val){ ?>
	                    <option value="<?php echo $key ?>"><?php echo $val ?></option>
	                <?php } ?>
	            </select>
	        </div>
	    </div>

        <div class="form-item even" id="multi-select-main-container" style="display:none">
            <label class="label-desc gray" for="department" id="category_selected"></label>
            <div  class="multiselect-input-wrap" id="multi-select-container"></div>
        </div>

        <div class="form-item odd" id="multi-select-employee-main-container">
            <label class="label-desc gray" for="department" id="employee_selected">Employee:</label>
            <div class="multiselect-input-wrap" id="multi-select-employee-container">
                <select id="employee" multiple="multiple" class="multi-select"  style="width:400px;" name="employee[]">
                </select>
            </div>
        </div>

    </div>
    <div class="icon-label-group">
        <div class="icon-label">
            <a rel="action-addnewparticipants" class="icon-16-add add_new_participants" href="javascript:void(NULL)" onclick="">
                <span>Add Participants</span>
            </a>            
        </div>
    </div>
</div>

<br />

<table id="module-participant" style="width:100%;" class="default-table boxtype">
    <colgroup width="15%"></colgroup>
    <thead>
	    <tr>
	        <th style="vertical-align:middle;" class="odd">
	        	Employee Name
	        </th>
	        <th style="vertical-align:middle;" class="odd">
	        	Status
	        </th>
	        <th style="vertical-align:middle;" class="odd">
	        	No Show
	        </th>
            <th style="vertical-align:middle;" class="odd">
                Remarks
            </th>
    		<th class="odd">
    			<span>&nbsp;</span>
    		</th>
	    </tr>
    </thead>
    <tbody>
        <?php 

        $readonly = '';

        if($immediate_superior == 1){
            $readonly = 'disabled="disabled"';
        }


        foreach( $participant as $participant_info ){ 

            $rand = rand(1,10000);
        ?>
            <tr>

                <td style="text-align:center; vertical-align:middle;"><?= $participant_info['name'] ?></td>
                <td style="text-align:center; vertical-align:middle;">

                    <?php if( $participant_info['participant_status_id'] != 3 ){ ?>
                    <select name="participants[<?= $rand ?>][status]" <?php echo $readonly; ?> class="participant_status">
                    <?php foreach( $participant_status_list as $participant_status ){ ?>
                        <option value="<?= $participant_status['participant_status_id'] ?>" <?php if( $participant_status['participant_status_id'] == $participant_info['participant_status_id'] ){ echo "selected"; } ?> ><?= $participant_status['participant_status'] ?></option>
                    <?php } ?>
                    </select>
                    <?php }else{ ?>
                        Cancelled
                    <?php } ?>
                </td>
                <td style="text-align:center; vertical-align:middle;">
                    <input type="radio" name="participants[<?= $rand ?>][no_show]" <?php echo $readonly; ?> class="no_show_yes" value="1" <?php if( $participant_info['no_show'] == 1 ){ echo "checked"; } ?> />Yes
                    <input type="radio" name="participants[<?= $rand ?>][no_show]" <?php echo $readonly; ?> class="no_show_no" value="0" <?php if( $participant_info['no_show'] == 0 ){ echo "checked"; } ?> />No
                </td>
                <td style="text-align:center; vertical-align:middle;">
                    <textarea name="participants[<?= $rand ?>][remarks]" class="remarks"><?= $participant_info['remarks'] ?></textarea>
                </td>
                <td style="text-align:center; vertical-align:middle;">
                    <?php if( $this->user_access[$this->module_id]['post'] == 1 ){  ?><a class="icon-button icon-16-delete delete-single delete-participant" href="javascript:void(0)" container="jqgridcontainer" tooltip="Delete"></a><?php } ?>
                    <input type="hidden" class="participants" name="participants[<?= $rand ?>][id]" value="<?= $participant_info['employee_id'] ?>" />
                    <input type="hidden" class="training_application_id" name="participants[<?= $rand ?>][training_application_id]" value="<?= $participant_info['training_application_id'] ?>" />
                </td>
            </tr>

        <?php } ?>
    </tbody>
</table>

<br />

<fieldset>
<div class="form-item odd">
    <label for="date" class="label-desc gray">Total Confirmed:</label>
    <div class="text-input-wrap">               
        <input type="text" name="total_confirmed" readonly="" value="0" style="width:20%;" class="input-text total_confirmed">
    </div>                                    
</div>
</fieldset>

