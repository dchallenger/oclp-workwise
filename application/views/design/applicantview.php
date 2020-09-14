<?php  $this->load->view($this->userinfo['rtheme'].'/design/table-of-contents')?>

<!-- EDIT VIEW 
----------------------------------------------------------------------------------->

<div class="wizard-leftcol">
  <ul>
    <li class="wizard-grayed"><a href="javascript:(void)"><span>1</span>Basic Information</a></li>
    <li class="wizard-active"><a href="javascript:(void)"><span>2</span>Personal</a></li>
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
    <li class="wizard-grayed"><a href="javascript:(void)"><span>3</span>Educational Background</a></li>
    <li class="wizard-grayed"><a href="javascript:(void)"><span>4</span>Trainings</a></li>
    <li class="wizard-grayed"><a href="javascript:(void)"><span>5</span>Skills</a></li>
    <li class="wizard-grayed"><a href="javascript:(void)"><span>6</span>Family</a></li>
    <li class="wizard-grayed"><a href="javascript:(void)"><span>7</span>Employment History</a></li>
    <li class="wizard-grayed"><a href="javascript:(void)"><span>8</span>Character References</a></li>
  </ul>
</div>
<div class="wizard-rightcol">
  <div class="wizard-header">
  
    <div class="page-navigator align-right">
      <div class="btn-prev-disabled"> 
          <a href="javascript:void(0)">
          <span>Prev</span></a>
      </div>
      <div class="btn-prev hidden">
      		<a onclick="prev_wizard()" href="javascript:void(0)">
          <span>Prev</span></a>
      </div>
      <div class="btn-next">
      		<a onclick="next_wizard()" href="javascript:void(0)">
          <span>Next</span></a>
      </div>
      <div class="btn-next-disabled hidden"> 
      		<a href="javascript:void(0)">
          <span>Next</span></a>
      </div>
    </div>
    
    <div class="icon-label-group align-right">
      <div class="icon-label"> 
      		<a onclick="ajax_save('', 0)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
          <span>Save</span></a> 
      </div>
      <div class="icon-label">
      		<a onclick="ajax_save('back', 0)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back">
          <span>Save &amp; Back</span></a>
       </div>
      <div class="icon-label">
      		<a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list">
          <span>Back to list</span></a>
      </div>
    </div>
    
  </div>
  <form enctype="multipart/form-data" method="post" id="record-form" name="record-form" class="style2 edit-view">
    <input type="hidden" value="-1" id="record_id" rel="dynamic" name="record_id">
    <input type="hidden" value="" id="prev_search_str" name="prev_search_str">
    <input type="hidden" value="all" id="prev_search_field" name="prev_search_field">
    <input type="hidden" value="" id="prev_search_option" name="prev_search_option">
    <input type="hidden" value="" id="prev_search_page" name="prev_search_page">
    <div id="form-div">
      <div class="wizard-type-form hidden current-wizard wizard-first" id="fg-35" fg_id="35" style="display: block;">
        <h3 class="form-head">Basic Information</h3>
        <div class="col-2-form">
          <div class="form-item odd">
            <label class="label-desc gray" for="firstname"> First Name: <span class="red font-large">*</span> </label>
            <div class="text-input-wrap">
              <input type="text" class="input-text" value="" id="firstname" name="firstname">
            </div>
          </div>
          <div class="form-item even">
            <label class="label-desc gray" for="lastname"> Last Name: <span class="red font-large">*</span> </label>
            <div class="text-input-wrap">
              <input type="text" class="input-text" value="" id="lastname" name="lastname">
            </div>
          </div>
          <div class="form-item odd">
            <label class="label-desc gray" for="middlename"> Middle Name: <span class="red font-large">*</span> </label>
            <div class="text-input-wrap">
              <input type="text" class="input-text" value="" id="middlename" name="middlename">
            </div>
          </div>
          <div class="form-item even">
            <label class="label-desc gray" for="maidenname"> Maiden Name: </label>
            <div class="text-input-wrap">
              <input type="text" class="input-text" value="" id="maidenname" name="maidenname">
            </div>
          </div>
          <div class="form-item odd">
            <label class="label-desc gray" for="company_id"> Company: <span class="red font-large">*</span> </label>
            <div class="select-input-wrap">
              <select id="company_id" name="company_id">
                <option value="">Select...</option>
                <option value="1">HDI System Technologies</option>
              </select>
            </div>
          </div>
          <div class="form-item even">
            <label class="label-desc gray" for="application_date"> Application Date: <span class="red font-large">*</span> </label>
            <div class="text-input-wrap">
              <input type="hidden" id="application_date" name="application_date" value="">
              <input type="text" disabled="disabled" class="input-text datepicker disabled hasDatepicker" value="" id="application_date-temp" name="application_date-temp">
              <img class="ui-datepicker-trigger" src="http://localhost/hdi.resource/themes/blue/icons/calendar-month.png" alt="" title=""></div>
          </div>
          <div class="form-item odd">
            <label class="label-desc gray" for="birth_date"> Birth Date: <span class="red font-large">*</span> </label>
            <div class="text-input-wrap">
              <input type="hidden" id="birth_date" name="birth_date" value="">
              <input type="text" disabled="disabled" class="input-text datepicker disabled hasDatepicker" value="" id="birth_date-temp" name="birth_date-temp">
              <img class="ui-datepicker-trigger" src="http://localhost/hdi.resource/themes/blue/icons/calendar-month.png" alt="" title=""></div>
          </div>
          <div class="form-item even">
            <label class="label-desc gray" for="address"> Present Address: <span class="red font-large">*</span> </label>
            <div class="textarea-input-wrap">
              <textarea class="input-textarea" id="address" name="address" rows="5"></textarea>
            </div>
          </div>
          <div class="form-item odd">
            <label class="label-desc gray" for="referred_by"> Referred By: </label>
            <div class="text-input-wrap">
              <input type="text" class="input-text" value="" id="referred_by" name="referred_by">
            </div>
          </div>
          <div class="form-item even">
            <label class="label-desc gray" for="expected_salary"> Salary Expectation: </label>
            <div class="text-input-wrap">
              <input type="text" class="input-text text-right" value="" id="expected_salary" name="expected_salary">
            </div>
          </div>
          <div class="form-item odd">
            <label class="label-desc gray" for="position_id"> Preferred Position: </label>
            <div class="select-input-wrap">
              <select id="position_id" name="position_id">
                <option value="">Select...</option>
                <option value="2">Administrator</option>
                <option value="4">Faculty</option>
                <option value="3">Student</option>
                <option value="1">Super Administrator</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
  
		<div class="page-navigator align-right">
      <div class="btn-prev-disabled"> 
          <a href="javascript:void(0)">
          <span>Prev</span></a>
      </div>
      <div class="btn-prev hidden">
      		<a onclick="prev_wizard()" href="javascript:void(0)">
          <span>Prev</span></a>
      </div>
      <div class="btn-next">
      		<a onclick="next_wizard()" href="javascript:void(0)">
          <span>Next</span></a>
      </div>
      <div class="btn-next-disabled hidden"> 
      		<a href="javascript:void(0)">
          <span>Next</span></a>
      </div>
    </div>
    
    <div class="icon-label-group align-right">
      <div class="icon-label"> 
      		<a onclick="ajax_save('', 0)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> 
          <span>Save</span></a> 
      </div>
      <div class="icon-label">
      		<a onclick="ajax_save('back', 0)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back">
          <span>Save &amp; Back</span></a>
       </div>
      <div class="icon-label">
      		<a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list">
          <span>Back to list</span></a>
      </div>
    </div>
  
  
  
</div>
<hr />
<div class="spacer"></div>

<!-- INFO VIEW 
----------------------------------------------------------------------------------->

<div class="wizard-leftcol">
  <ul>
    <li><a href="javascript:(void)"><span>1</span>Basic Information</a></li>
    <li class="wizard-active"><a href="javascript:(void)"><span>2</span>Personal</a></li>
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
    <li><a href="javascript:(void)"><span>3</span>Educational Background</a></li>
    <li><a href="javascript:(void)"><span>4</span>Trainings</a></li>
    <li><a href="javascript:(void)"><span>5</span>Skills</a></li>
    <li><a href="javascript:(void)"><span>6</span>Family</a></li>
    <li><a href="javascript:(void)"><span>7</span>Employment History</a></li>
    <li><a href="javascript:(void)"><span>8</span>Character References</a></li>
  </ul>
</div>
<div class="wizard-rightcol">
  <div class="wizard-header"> <img src="<?php echo base_url() . $this->userinfo['theme'] ?>/images/wizard-iconpic.png" />
    <h2>Reyes, Benjamin</h2>
    
    <div class="page-navigator align-right">
      <div class="btn-prev-disabled"> 
          <a href="javascript:void(0)">
          <span>Prev</span></a>
      </div>
      <div class="btn-prev hidden">
      		<a onclick="prev_wizard()" href="javascript:void(0)">
          <span>Prev</span></a>
      </div>
      <div class="btn-next">
      		<a onclick="next_wizard()" href="javascript:void(0)">
          <span>Next</span></a>
      </div>
      <div class="btn-next-disabled hidden"> 
      		<a href="javascript:void(0)">
          <span>Next</span></a>
      </div>
    </div>
    
    <div class="icon-label-group align-right">
      <div class="icon-label">
      		<a onclick="edit()" href="javascript:void(0);" class="icon-16-edit">
          <span>Edit</span></a>
      </div>
      <div class="icon-label"> 
          <a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list"> 
          <span>Back to list</span> </a>
      </div>
    </div>
  </div>
  <div class="wizard-type-form hidden current-wizard wizard-first" id="fg-35" fg_id="35" style="display: block;">
    <form enctype="multipart/form-data" method="post" id="record-form" name="record-form" class="style2 detail-view">
      <input type="hidden" value="3" id="record_id" name="record_id">
      <input type="hidden" value="3" id="return_record_id" name="return_record_id">
      <input type="hidden" value="http://localhost/hdi.resource/recruitment/applicants/detail" id="previous_page" name="previous_page">
      <input type="hidden" value="" id="prev_search_str" name="prev_search_str">
      <input type="hidden" value="all" id="prev_search_field" name="prev_search_field">
      <input type="hidden" value="" id="prev_search_option" name="prev_search_option">
      <h3 class="form-head">Basic Information</h3>
      <div class="col-2-form view">
        <div class="form-item view odd">
          <label class="label-desc view gray" for="firstname">First Name:</label>
          <div class="text-input-wrap"> teteSSSS </div>
        </div>
        <div class="form-item view even">
          <label class="label-desc view gray" for="lastname">Last Name:</label>
          <div class="text-input-wrap"> test </div>
        </div>
        <div class="form-item view odd">
          <label class="label-desc view gray" for="middlename">Middle Name:</label>
          <div class="text-input-wrap"> test </div>
        </div>
        <div class="form-item view even">
          <label class="label-desc view gray" for="maidenname">Maiden Name:</label>
          <div class="text-input-wrap"> test </div>
        </div>
        <div class="form-item view odd">
          <label class="label-desc view gray" for="company_id">Company:</label>
          <div class="text-input-wrap"> HDI System Technologies </div>
        </div>
        <div class="form-item view even">
          <label class="label-desc view gray" for="application_date">Application Date:</label>
          <div class="text-input-wrap"> 10/04/2011 </div>
        </div>
        <div class="form-item view odd">
          <label class="label-desc view gray" for="birth_date">Birth Date:</label>
          <div class="text-input-wrap"> 10/05/2011 </div>
        </div>
        <div class="form-item view even">
          <label class="label-desc view gray" for="address">Present Address:</label>
          <div class="text-input-wrap"> dawdawdawd </div>
        </div>
        <div class="form-item view odd">
          <label class="label-desc view gray" for="referred_by">Referred By:</label>
          <div class="text-input-wrap"> &nbsp; </div>
        </div>
        <div class="form-item view even">
          <label class="label-desc view gray" for="expected_salary">Salary Expectation:</label>
          <div class="text-input-wrap"> &nbsp; </div>
        </div>
        <div class="form-item view odd">
          <label class="label-desc view gray" for="position_id">Preferred Position:</label>
          <div class="text-input-wrap"> Super Administrator </div>
        </div>
      </div>
    </form>
  </div>
  
  <div class="page-navigator align-right">
      <div class="btn-prev-disabled"> 
          <a href="javascript:void(0)">
          <span>Prev</span></a>
      </div>
      <div class="btn-prev hidden">
      		<a onclick="prev_wizard()" href="javascript:void(0)">
          <span>Prev</span></a>
      </div>
      <div class="btn-next">
      		<a onclick="next_wizard()" href="javascript:void(0)">
          <span>Next</span></a>
      </div>
      <div class="btn-next-disabled hidden"> 
      		<a href="javascript:void(0)">
          <span>Next</span></a>
      </div>
    </div>
  
  <div class="icon-label-group align-right">
    <div class="icon-label">
    		<a onclick="edit()" href="javascript:void(0);" class="icon-16-edit">
        <span>Edit</span></a>
    </div>
    <div class="icon-label">
    		<a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list"> 
        <span>Back to list</span></a>
    </div>
  </div>
</div>

<!-- INFO VIEW (with additional edit buttons)
----------------------------------------------------------------------------------->
<hr />
<div class="spacer"></div>
<div class="wizard-leftcol">
  <ul>
    <li><a href="javascript:(void)"><span>1</span>Basic Information</a></li>
    <li class="wizard-active"><a href="javascript:(void)"><span>2</span>Personal</a></li>
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
    <li><a href="javascript:(void)"><span>3</span>Educational Background</a></li>
    <li><a href="javascript:(void)"><span>4</span>Trainings</a></li>
    <li><a href="javascript:(void)"><span>5</span>Skills</a></li>
    <li><a href="javascript:(void)"><span>6</span>Family</a></li>
    <li><a href="javascript:(void)"><span>7</span>Employment History</a></li>
    <li><a href="javascript:(void)"><span>8</span>Character References</a></li>
  </ul>
  
  
</div>
<div class="wizard-rightcol">
  <div class="wizard-header"> <img src="<?php echo base_url() . $this->userinfo['theme'] ?>/images/wizard-iconpic.png" />
    <h2>Reyes, Benjamin</h2>
    
    <div class="page-navigator align-right">
      <div class="btn-prev-disabled"> 
          <a href="javascript:void(0)">
          <span>Prev</span></a>
      </div>
      <div class="btn-prev hidden">
      		<a onclick="prev_wizard()" href="javascript:void(0)">
          <span>Prev</span></a>
      </div>
      <div class="btn-next">
      		<a onclick="next_wizard()" href="javascript:void(0)">
          <span>Next</span></a>
      </div>
      <div class="btn-next-disabled hidden"> 
      		<a href="javascript:void(0)">
          <span>Next</span></a>
      </div>
    </div>
    
    <div class="icon-label-group align-right">
      <div class="icon-label">
      		<a onclick="edit()" href="javascript:void(0);" class="icon-16-add icon-16-add-listview">
          <span>Add</span></a>
      </div>
      <div class="icon-label">
      		<a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list">
          <span>Back to list</span></a>
      </div>
    </div>
  </div>
  <div class="wizard-type-form hidden current-wizard wizard-first" id="fg-35" fg_id="35" style="display: block;">
    <form enctype="multipart/form-data" method="post" id="record-form" name="record-form" class="style2 detail-view">
      <input type="hidden" value="3" id="record_id" name="record_id">
      <input type="hidden" value="3" id="return_record_id" name="return_record_id">
      <input type="hidden" value="http://localhost/hdi.resource/recruitment/applicants/detail" id="previous_page" name="previous_page">
      <input type="hidden" value="" id="prev_search_str" name="prev_search_str">
      <input type="hidden" value="all" id="prev_search_field" name="prev_search_field">
      <input type="hidden" value="" id="prev_search_option" name="prev_search_option">
      <h3 class="form-head">Family</h3>
      <h3 class="form-head">
        <div class="align-right"><span class="fh-edit"><a href="javascript:void(0)">EDIT</a></span> <span class="fh-delete"><a href="#">DELETE</a></span></div>
      </h3>
      <div class="col-2-form view">
        <div class="form-item view odd">
          <label class="label-desc view gray" for="firstname">Name:</label>
          <div class="text-input-wrap"> teteSSSS </div>
        </div>
        <div class="form-item view even">
          <label class="label-desc view gray" for="lastname">Relationship:</label>
          <div class="text-input-wrap"> test </div>
        </div>
        <div class="form-item view odd">
          <label class="label-desc view gray" for="middlename">Birthdate:</label>
          <div class="text-input-wrap"> test </div>
        </div>
        <div class="form-item view even">
          <label class="label-desc view gray" for="maidenname">Occupation:</label>
          <div class="text-input-wrap"> test </div>
        </div>
        <div class="form-item view odd">
          <label class="label-desc view gray" for="company_id">Employer:</label>
          <div class="text-input-wrap"> HDI System Technologies </div>
        </div>
      </div>
      <h3 class="form-head">
        <div class="align-right"><span class="fh-edit"><a href="javascript:void(0)">EDIT</a></span> <span class="fh-delete"><a href="#">DELETE</a></span></div>
      </h3>
      <div class="col-2-form view">
        <div class="form-item view odd">
          <label class="label-desc view gray" for="firstname">Name:</label>
          <div class="text-input-wrap"> teteSSSS </div>
        </div>
        <div class="form-item view even">
          <label class="label-desc view gray" for="lastname">Relationship:</label>
          <div class="text-input-wrap"> test </div>
        </div>
        <div class="form-item view odd">
          <label class="label-desc view gray" for="middlename">Birthdate:</label>
          <div class="text-input-wrap"> test </div>
        </div>
        <div class="form-item view even">
          <label class="label-desc view gray" for="maidenname">Occupation:</label>
          <div class="text-input-wrap"> test </div>
        </div>
        <div class="form-item view odd">
          <label class="label-desc view gray" for="company_id">Employer:</label>
          <div class="text-input-wrap"> HDI System Technologies </div>
        </div>
      </div>
    </form>
  </div>
  
  <div class="page-navigator align-right">
      <div class="btn-prev-disabled"> 
          <a href="javascript:void(0)">
          <span>Prev</span></a>
      </div>
      <div class="btn-prev hidden">
      		<a onclick="prev_wizard()" href="javascript:void(0)">
          <span>Prev</span></a>
      </div>
      <div class="btn-next">
      		<a onclick="next_wizard()" href="javascript:void(0)">
          <span>Next</span></a>
      </div>
      <div class="btn-next-disabled hidden"> 
      		<a href="javascript:void(0)">
          <span>Next</span></a>
      </div>
    </div>
  
  <div class="icon-label-group align-right">
    <div class="icon-label">
    		<a onclick="edit()" href="javascript:void(0);" class="icon-16-add icon-16-add-listview">
        <span>Add</span></a>
    </div>
    <div class="icon-label">
    		<a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list">
        <span>Back to list</span></a>
    </div>
  </div>
</div>

<!-- END MAIN CONTENT -->
</dl>
