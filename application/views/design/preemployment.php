<?php  $this->load->view($this->userinfo['rtheme'].'/design/table-of-contents')?>
  
  <div class="leftpane">
    <ul class="list-counters2">
      <li> <a href="http://localhost/hdi.resource/recruitment/preemployment_onboarding"> <span class="ctr-orange">10</span> <span>New Employee On-boarding Checklist</span></a> </li>
      <li> <a href="http://localhost/hdi.resource/recruitment/preemployment_checklist"> <span class="ctr-orange inactive">0</span> <span>Pre-Employment Checklist</span></a> </li>
      <li> <a href="http://localhost/hdi.resource/recruitment/preemployment_background"> <span class="ctr-orange">1</span> <span>Background Investigation Form</span></a> </li>
      <li> <a href="http://localhost/hdi.resource/recruitment/preemployment_buddy"> <span class="ctr-orange">1</span> <span>Buddy Evaluation Form</span></a> </li>
      <li> <a href="http://localhost/hdi.resource/recruitment/preemployment_schoolverification"> <span class="ctr-orange">1</span> <span>School Verification Form</span></a> </li>
      <li> <a href="http://localhost/hdi.resource/#"> <span class="ctr-orange inactive">0</span> <span>Home/Neighborhood Verification Form</span></a> </li>
      <li> <a href="http://localhost/hdi.resource/recruitment/preemployment_201"> <span class="ctr-orange">0</span> <span>Employee 201</span></a> </li>
    </ul>
  </div>
  <div class="rightpane">
    <div class="wizard-header">
      <div class="icon-label-group align-right">
        <div class="icon-label"> <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);"> <span>Back to list</span> </a> </div>
      </div>
    </div>
    
    <!-- #Preemployment Data-->
    <div class="col-2-form view">
      <div class="form-item view odd">
        <label class="label-desc view gray">Applicant Name:</label>
        <div class="text-input-wrap">awdawdawd dawdawd</div>
      </div>
      <div class="form-item view even">
        <label class="label-desc view gray">Position / Requested By:</label>
        <div class="text-input-wrap">Super Admin HDI</div>
      </div>
      <div class="form-item view odd">
        <label class="label-desc view gray">Subsidiary / Department:</label>
        <div class="text-input-wrap">HDI RESOURCE, INC. - Payroll and CompBen</div>
      </div>
    </div>
    <div class="spacer"></div>
    <!-- Dummy form for ajax -->
    <form method="post" name="dummy-form">
      <input type="hidden" value="" id="record_id" name="record_id">
    </form>
    <!-- #END Preemployment Data-->
    <form enctype="multipart/form-data" method="post" id="record-form" name="record-form" class="style2 detail-view">
      <input type="hidden" value="6" id="record_id" name="record_id">
      <input type="hidden" value="6" id="return_record_id" name="return_record_id">
      <input type="hidden" value="http://localhost/hdi.resource/recruitment/preemployment/detail" id="previous_page" name="previous_page">
      <input type="hidden" value="" id="prev_search_str" name="prev_search_str">
      <input type="hidden" value="all" id="prev_search_field" name="prev_search_field">
      <input type="hidden" value="" id="prev_search_option" name="prev_search_option">
    </form>
    <div class="clear"></div>
    <ul class="rightpane-list">
      <li rel="recruitment/preemployment_onboarding"> <span class="ctr">1</span>
        <div><a href="javascript:void(0)" class="trigger-show">New Employee On-boarding Checklist </a> <br>
          <small>Completed by: Admin HDI on Nov 14, 2011 11:21am</small></div>
         <span class="pe-actions hidden"><a href="#">Edit</a> | <a href="#">Delete</a> | <a href="#">Print</a></span>
      </li>
      <li rel="recruitment/preemployment_checklist"> <span class="ctr">2</span>
        <div><a href="javascript:void(0)" class="trigger-show"> Pre-Employment Checklist </a> <br>
          <small>Completed by: Admin HDI on Nov 14, 2011 11:21am</small> </div>
          <span class="pe-actions hidden"><a href="#">Edit</a> | <a href="#">Delete</a> | <a href="#">Print</a></span>
      </li>
      <li rel="recruitment/preemployment_background"> <span class="ctr">3</span>
        <div><a href="javascript:void(0)" class="trigger-show"> Background Investigation Form</a> <br>
          <small>Completed by: Admin HDI on Nov 14, 2011 11:21am</small></div>
          <span class="pe-actions hidden"><a href="#">Edit</a> | <a href="#">Delete</a> | <a href="#">Print</a></span>
      </li>
      <li rel="recruitment/preemployment_buddy"> <span class="ctr">4</span>
        <div><a href="javascript:void(0)" class="trigger-show"> Buddy Evaluation Form</a> <br>
          <small>Completed by: Admin HDI on Nov 14, 2011 11:21am</small></div>
          <span class="pe-actions hidden"><a href="#">Edit</a> | <a href="#">Delete</a> | <a href="#">Print</a></span>
      </li>
      <li rel="recruitment/preemployment_schoolverification"> <span class="ctr">5</span>
        <div><a href="javascript:void(0)" class="trigger-show"> School Verification Form </a> <br>
          <small>Completed by: Admin HDI on Nov 14, 2011 11:21am</small></div>
          <span class="pe-actions hidden"><a href="#">Edit</a> | <a href="#">Delete</a> | <a href="#">Print</a></span>
      </li>
      <li rel="#"> <span class="ctr">6</span>
        <div><a href="javascript:void(0)" class="trigger-show"> Home/Neighborhood Verification Form </a> <br>
          <small>Completed by: Admin HDI on Nov 14, 2011 11:21am</small></div>
          <span class="pe-actions hidden"><a href="#">Edit</a> | <a href="#">Delete</a> | <a href="#">Print</a></span>
      </li>
      <li rel="recruitment/preemployment_201"> <span class="ctr">7</span>
        <div><a href="javascript:void(0)" class="trigger-show"> Employee 201 </a><br>
          <small>Completed by: Admin HDI on Nov 14, 2011 11:21am</small></div>
          <span class="pe-actions hidden"><a href="#">Edit</a> | <a href="#">Delete</a> | <a href="#">Print</a></span>
      </li>
    </ul>
    <div class="icon-label-group align-right">
      <div class="icon-label"> <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);"> <span>Back to list</span> </a> </div>
    </div>
  </div>
  
  <!-- EDIT END
-----------------------------------------------------------------------------------> 
</div>
