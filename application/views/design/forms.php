<?php  $this->load->view($this->userinfo['rtheme'].'/design/table-of-contents')?>

  <h2>VIEW MODE</h2>
  <hr />
  <h3 class="form-head">Class: col-2-form view <small><em>(for short labels)</em></small></h3>
  <div class="col-2-form view">
    <div class="form-item view odd">
      <label class="label-desc view gray" for="short_name">Short Name:</label>
      <div class="text-input-wrap"> Dashboard </div>
    </div>
    <div class="form-item view even">
      <label class="label-desc view gray" for="long_name">Long Name:</label>
      <div class="text-input-wrap"> Dashboard </div>
    </div>
    <div class="form-item view odd">
      <label class="label-desc view gray" for="parent_id">Parent Module:</label>
      <div class="text-input-wrap"> Top Level </div>
    </div>
    <div class="form-item view even">
      <label class="label-desc view gray" for="sequence">Sequence:</label>
      <div class="text-input-wrap"> 1 </div>
    </div>
    <div class="form-item view odd">
      <label class="label-desc view gray" for="sm_icon">Small Icon:</label>
      <div class="text-input-wrap"> icon-dashboard.png </div>
    </div>
    <div class="form-item view even">
      <label class="label-desc view gray" for="big_icon">Big Icon:</label>
      <div class="text-input-wrap"> &nbsp; </div>
    </div>
    <div class="form-item view odd">
      <label class="label-desc view gray" for="wizard_form">Wizard Form:</label>
      <div class="text-input-wrap"> No </div>
    </div>
    <div class="form-item view even">
      <label class="label-desc view gray" for="inactive">Inactive:</label>
      <div class="text-input-wrap"> No </div>
    </div>
  </div>
  <h3 class="form-head">Class: col-1-form view <small><em>(for long labels)</em></small></h3>
  <div class="col-1-form view">
    <div class="form-item view odd">
      <label class="label-desc view gray" for="short_name">This should be a very long label, could occupy up to two lines. This should be a very long label, could occupy up to two lines:</label>
      <div class="text-input-wrap"> Dashboard </div>
    </div>
    <div class="form-item view even">
      <label class="label-desc view gray" for="long_name">This should be a very long label, could occupy up to two lines:</label>
      <div class="text-input-wrap"> Dashboard </div>
    </div>
    <div class="form-item view odd">
      <label class="label-desc view gray" for="long_name">This should be a very long label, could occupy up to two lines:</label>
      <div class="text-input-wrap"> Top Level </div>
    </div>
    <div class="form-item view even">
      <label class="label-desc view gray" for="long_name">This should be a very long label, could occupy up to two lines:</label>
      <div class="text-input-wrap"> 1 </div>
    </div>
    <div class="form-item view odd">
      <label class="label-desc view gray" for="long_name">This should be a very long label, could occupy up to two lines:</label>
      <div class="text-input-wrap"> icon-dashboard.png </div>
    </div>
    <div class="form-item view even">
      <label class="label-desc view gray" for="long_name">This should be a very long label, could occupy up to two lines:</label>
      <div class="text-input-wrap"> &nbsp; </div>
    </div>
    <div class="form-item view odd">
      <label class="label-desc view gray" for="long_name">This should be a very long label, could occupy up to two lines:</label>
      <div class="text-input-wrap"> No </div>
    </div>
    <div class="form-item view even">
      <label class="label-desc view gray" for="long_name">This should be a very long label, could occupy up to two lines:</label>
      <div class="text-input-wrap"> No </div>
    </div>
  </div>
  <h2>ADD/EDIT MODE</h2>
  <hr />
  <div class="style2">
    <h3 class="form-head">Class: col-2-form <small><em>(for short labels)</em></small></h3>
    <div class="col-2-form">
      <div class="form-item odd ">
        <label class="label-desc gray" for="short_name"> Short Name: </label>
        <div class="text-input-wrap">
          <input type="text" class="input-text" value="Dashboard" id="short_name" name="short_name">
        </div>
      </div>
      <div class="form-item even ">
        <label class="label-desc gray" for="long_name"> Long Name: </label>
        <div class="text-input-wrap">
          <input type="text" class="input-text" value="Dashboard" id="long_name" name="long_name">
        </div>
      </div>
      <div class="form-item odd ">
        <label class="label-desc gray" for="parent_id"> Parent Module: </label>
        <div class="text-input-wrap">
          <input type="hidden" class="input-text" value="" id="parent_id" name="parent_id">
          <input type="text" disabled="disabled" style="width:75%" class="input-text disabled" value="" id="parent_id-name" name="parent_id-name">
          <span class="icon-group"> <a onclick="getRelatedModule('3', 'parent_id')" href="javascript:void(0);" class="icon-button icon-16-add"></a><a onclick="clearField('parent_id')" href="javascript:void(0);" class="icon-button icon-16-minus"></a> </span></div>
      </div>
      <div class="form-item even ">
        <label class="label-desc gray" for="sequence"> Sequence: </label>
        <div class="text-input-wrap">
          <input type="text" class="input-text text-right" value="1" id="sequence" name="sequence">
        </div>
      </div>
      <div class="form-item odd ">
        <label class="label-desc gray" for="sm_icon"> Small Icon: </label>
        <div class="text-input-wrap">
          <input type="text" class="input-text" value="icon-dashboard.png" id="sm_icon" name="sm_icon">
        </div>
      </div>
      <div class="form-item even ">
        <label class="label-desc gray" for="big_icon"> Big Icon: </label>
        <div class="text-input-wrap">
          <input type="text" class="input-text" value="" id="big_icon" name="big_icon">
        </div>
      </div>
      <div class="form-item odd ">
        <label class="label-desc gray" for="wizard_form"> Wizard Form: </label>
        <div class="radio-input-wrap">
          <input type="radio" class="input-radio" value="1" id="wizard_form-yes" name="wizard_form">
          <label class="check-radio-label gray" for="wizard_form-yes">Yes</label>
          <input type="radio" checked="checked" class="input-radio" value="0" id="wizard_form-no" name="wizard_form">
          <label class="check-radio-label gray" for="wizard_form-no">No</label>
        </div>
      </div>
      <div class="form-item even ">
        <label class="label-desc gray" for="inactive"> Inactive: </label>
        <div class="radio-input-wrap">
          <input type="radio" class="input-radio" value="1" id="inactive-yes" name="inactive">
          <label class="check-radio-label gray" for="inactive-yes">Yes</label>
          <input type="radio" checked="checked" class="input-radio" value="0" id="inactive-no" name="inactive">
          <label class="check-radio-label gray" for="inactive-no">No</label>
        </div>
      </div>
      <div class="form-item odd ">
                                                                            <label class="label-desc gray" for="company_id">
                                                Under what company:
                                                <span class="red font-large">*</span>                                                                                        </label>
                                        <div class="select-input-wrap"><select id="company_id" name="company_id"><option value="">Select...</option><option value="1">ERMINLAND REALTY CORPORATION</option><option value="2">HDI ADVENTURES, INC.</option><option value="6">HDI HOLDINGS PHILIPPINES, INC.</option><option value="9">HDI NETWORK PHILIPPINES, INC.</option><option value="13">HDI RESOURCE, INC.</option><option value="4">HDI SECURITIES, INC.</option><option value="5">HDI STOPOVERS, INC.</option><option value="3">HDI SYSTEM TECHNOLOGIES</option><option value="7">HIGH DESERT PHILIPPINES, INC.</option><option value="8">HILLCROFT PHILIPPINES, INC.</option><option value="11">STANFORD FINANCE</option>
					</select>
				</div>                                                                    </div>
    </div>
    <h3 class="form-head">Class: col-1-form</h3>
    <div class="col-1-form">
      <div class="form-item odd ">
        <label class="label-desc gray" for="offense"> Based on you company records and 201 file, has the subject employee been charged/found guilty of any offense or violation?: </label>
        <div class="radio-input-wrap">
          <input type="radio" class="input-radio" value="1" id="offense-yes" name="offense">
          <label class="check-radio-label gray" for="offense-yes">Yes</label>
          <input type="radio" checked="checked" class="input-radio" value="0" id="offense-no" name="offense">
          <label class="check-radio-label gray" for="offense-no">No</label>
        </div>
      </div>
      <div class="form-item even ">
        <label class="label-desc gray" for="offense_description"> If yes, please state date of commission of offense, and nature of offense: </label>
        <div class="textarea-input-wrap">
          <textarea class="input-textarea" id="offense_description" name="offense_description" rows="5"></textarea>
        </div>
      </div>
      <div class="form-item odd ">
        <label class="label-desc gray" for="awards"> Has the subject been given an award, citation, commendation, or promotion based on the actual performance on the job: </label>
        <div class="radio-input-wrap">
          <input type="radio" class="input-radio" value="1" id="awards-yes" name="awards">
          <label class="check-radio-label gray" for="awards-yes">Yes</label>
          <input type="radio" checked="checked" class="input-radio" value="0" id="awards-no" name="awards">
          <label class="check-radio-label gray" for="awards-no">No</label>
        </div>
      </div>
      <div class="form-item even ">
        <label class="label-desc gray" for="awards_description"> If yes, please specify: </label>
        <div class="textarea-input-wrap">
          <textarea class="input-textarea" id="awards_description" name="awards_description" rows="5"></textarea>
        </div>
      </div>
      <div class="form-item odd ">
        <label class="label-desc gray" for="started_working"> From your personal records, can you tell me when he/she started working with you and when he/she left: </label>
        <div class="text-input-wrap">
          <input type="text" class="input-text" value="" id="started_working" name="started_working">
        </div>
      </div>
      <div class="form-item even ">
        <label class="label-desc gray" for="financial_concerns"> Do you know of any personal/marital/financial concerns affecting the perfromance of this employee: </label>
        <div class="text-input-wrap">
          <input type="text" class="input-text" value="" id="financial_concerns" name="financial_concerns">
        </div>
      </div>
      <div class="form-item odd ">
        <label class="label-desc gray" for="real_reason"> In your opinion, what was the real reason he/she is no longer working in your organization: </label>
        <div class="text-input-wrap">
          <input type="text" class="input-text" value="" id="real_reason" name="real_reason">
        </div>
      </div>
    </div>
  </div>
</div>
<!-- #body-content-wrap --> 

