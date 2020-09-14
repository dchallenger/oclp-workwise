<!-- TREE VIEW -->
<script type="text/javascript" src="<?php echo base_url(); ?>lib/jqtreeview/jquery.treeview.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>lib/jqtreeview/jquery.cookie.js"></script>
<link rel="stylesheet" href="<?php echo base_url(); ?>lib/jqtreeview/jquery.treeview.css">
<script type="text/javascript">
		$(function() {
			$("#tree").treeview({
				collapsed: true,
				animated: "medium",
				control:"#sidetreecontrol",
				persist: "location"
			});
		})
</script>

<!-- TINY SCROLLBAR- -->
<script type="text/javascript" src="<?php echo base_url(); ?>lib/tinyscrollbar/jquery.tinyscrollbar.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url(); ?>lib/tinyscrollbar/tinyscrollbar.css">
<script type="text/javascript">
		$(document).ready(function(){
			$("#sidetree").tinyscrollbar();	
		});
	</script>	

<!-- start #page-head -->

<div id="page-head" class="page-info">
  <div id="page-title">
    <h2 class="page-title"><span class="title">WORK SCHED</span></h2>
  </div>
  <div id="page-desc" class="align-left">
    <p>
      <?=$this->detailview_description?>
    </p>
  </div>
  <?php                               
        // Page Nav Structure
        if ( isset($pnav) ) echo $pnav;                         
    ?>
  <div class="clear"></div>
</div>
<!-- end #page-head -->

<div class="sidebar-wrap">
  <div class="styleguide-links">
    <h3>Design Style Guide</h3>
    <?php  $this->load->view($this->userinfo['rtheme'].'/design/table-of-contents')?>
  </div>
  <div class="searchbar ui-widget">
    <input type="text" value="Search Site" class="input-text ui-autocomplete-input" onfocus="if (this.value == 'Search Site') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Search Site';}" id="searchbar" autocomplete="off" role="textbox" aria-autocomplete="list" aria-haspopup="true">
  </div>
</div>
<div id="body-content-wrap"> 
  
  <!-- EDIT START
----------------------------------------------------------------------------------->
  
  <div class="leftpane">
    <div id="sidetree" >
      <div id="sidetreecontrol" class="align-right"> <span class="icon-group"> <a href="javascript:void(0);" class="icon-button icon-16-tree-collapse" toolTip="Collapse All"></a> <a href="javascript:void(0);" class="icon-button icon-16-tree-expand" toolTip="Expand All"></a> </span> </div>
      <h3>EMPLOYEE LIST</h3>
      <hr>
     
  		   
  	
   
	
				         <ul id="tree">
        <li><a href="?/index.cfm"><strong>Home</strong></a>
          <ul>
            <li><a href="?/enewsletters/index.cfm">Airdrie eNewsletters </a></li>
            <li><a href="?/index.cfm">Airdrie Directories</a></li>
            <li><a href="?/economic_development/video/index.cfm">Airdrie Life Video</a></li>
            <li><a href="?/index.cfm">Airdrie News</a></li>
            <li><a href="?/index.cfm">Airdrie Quick Links</a></li>
            <li><a href="?http://weather.ibegin.com/ca/ab/airdrie/">Airdrie Weather</a></li>
            <li><a href="?/human_resources/index.cfm">Careers</a> | <a href="?/contact_us/index.cfm">Contact Us</a> | <a href="?/site_map/index.cfm">Site Map</a> | <a href="?/links/index.cfm">Links</a></li>
            <li><a href="?/calendars/index.cfm">Community Calendar </a></li>
            <li><a href="?/conditions_of_use/index.cfm">Conditions of Use and Privacy Statement</a></li>
            <li><a href="?/index.cfm">I'd like to find out about... </a></li>
            <li><a href="?/index.cfm">Opportunities</a></li>
            <li><a href="?/links/index.cfm">Resource Links</a></li>
            <li><a href="?/index.cfm">Special Notices</a></li>
          </ul>
        </li>
        <li><span><strong>City Services</strong></span>
          <ul>
            <li><a href="?/assessment/index.cfm">Assessment</a>
              <ul>
                <li><a href="?/assessment/assessment_faqs.cfm">Assessment FAQs</a></li>
                <li><a href="?/assessment/property_assessment_notices.cfm">2007 Property Assessment Notices</a></li>
                <li><a href="?http://www.creb.com/">CREB</a></li>
                <li><a href="?/assessment/non_residential_assessment_tax_comparisons.cfm">Non-Residential Assessment / Tax Comparisons</a></li>
                <li><a href="?/assessment/how_to_file_a_complaint.cfm">How to File a Complaint</a></li>
                <li><a href="?/assessment/supplementary_assessment_tax.cfm">Supplementary Assessment and Tax</a></li>
              </ul>
            </li>
            <li><a href="?/building_development/index.cfm">Building &amp; Development </a>
              <ul>
                <li><a href="?/building_inspections/index.cfm">Building Inspections</a>
                  <ul>
                    <li><a href="?/building_inspections/builder_forums.cfm">Builder Forums</a></li>
                    <li><a href="?/building_inspections/contact_us.cfm">Contact Us</a></li>
                    <li><a href="?/building_inspections/contractor_notices.cfm">Contractor Notices</a></li>
                    <li><a href="?/building_inspections/inspector_guidelines.cfm">Inspector Guidelines</a></li>
                    <li><a href="?/building_inspections/links.cfm">Links</a></li>
                    <li><a href="?/building_inspections/statistics_2007.cfm">Statistics</a>
                      <ul>
                        <li><a href="?/building_inspections/statistics_2006.cfm">Statistics 2006</a></li>
                        <li><a href="?/building_inspections/statistics_2005.cfm">Statistics 2005</a></li>
                      </ul>
                    </li>
                  </ul>
                </li>
                <li><a title="City Infrastructure" href="?/building_development/city_infrastructure/index.cfm">City Infrastructure</a>
                  <ul>
                    <li><a href="?/building_development/city_infrastructure/roadway_improvement.cfm">Roadway Improvement</a></li>
                    <li><a href="?/building_development/city_infrastructure/traffic.cfm">Traffic</a></li>
                    <li><a href="?/building_development/city_infrastructure/transportation_planning.cfm">Transportation &amp; Infrastructure Planning</a></li>
                    <li><a href="?/building_development/city_infrastructure/water_sewer_construction.cfm">Water &amp; Sewer Construction</a></li>
                  </ul>
                </li>
                <li><a title="Commercial/Industrial Development" href="?/building_development/commercial_industrial_development/index.cfm">Commercial / Industrial / Multi-Family Development</a>
                  <ul>
                    <li><a title="Call Before You Dig" href="?/building_development/commercial_industrial_development/call_before_you_dig.cfm">Call Before You Dig</a></li>
                    <li><a title="New Development" href="?/building_development/commercial_industrial_development/new_development.cfm">New Development</a></li>
                    <li><a title="Existing Development" href="?/building_development/commercial_industrial_development/existing_development.cfm">Existing Development</a></li>
                    <li><a title="Signage" href="?/building_development/commercial_industrial_development/signage.cfm">Signage</a></li>
                    <li><a title="Notice of Development" href="?/building_development/planning/notice_of_development/notice_of_development.cfm">Notice of Development</a></li>
                    <li><a title="Appeals" href="?/public_meetings/appeals/index.cfm">Appeals</a></li>
                    <li><a title="Customer Feedback" href="?/building_development/commercial_industrial_development/customer_feedback.cfm">Customer Feedback</a></li>
                    <li><a title="Certificate of Compliance" href="?/building_development/commercial_industrial_development/certificate_of_compliance.cfm">Certificate of Compliance</a></li>
                    <li><a title="Permit Applications &amp; Forms" href="?/building_development/commercial_industrial_development/permit_applications_forms.cfm">Permit Applications &amp; Forms</a></li>
                    <li><a title="Fees" href="?/building_development/commercial_industrial_development/fees.cfm">Fees</a></li>
                  </ul>
                </li>
                <li><a title="Residential Development" href="?/building_development/residential_development/index.cfm">Residential Development</a>
                  <ul>
                    <li><a title="Call Before You Dig" href="?/building_development/residential_construction/building_permit_requirements.cfm">Building Permit Requirements</a></li>
                    <li><a title="New Development" href="?/building_development/residential_construction/new_homes.cfm">New Homes</a></li>
                    <li><a title="Existing Development" href="?/building_development/residential_construction/basements.cfm">Basements</a></li>
                    <li><a title="Signage" href="?/building_development/commercial_industrial_development/call_before_you_dig.cfm">Call Before You Dig</a></li>
                    <li><a title="Decks" href="?/building_development/residential_development/decks.cfm">Decks</a></li>
                    <li><a title="Detached Garages or Accessory Building" href="?/building_development/residential_development/detached_garages_or_accessory_building.cfm">Detached Garages or Accessory Building</a></li>
                    <li><a title="Grading" href="?/building_development/residential_development/grading.cfm">Grading</a></li>
                    <li><a title="Fences" href="?/building_development/residential_development/fences.cfm">Fences</a></li>
                    <li><a title="Applications, Permits &amp; Checklists" href="?/building_development/residential_development/applications_permits_checklists.cfm">Applications, Permits &amp; Checklists</a></li>
                    <li><a title="Certificate of Compliance" href="?/building_development/commercial_industrial_development/certificate_of_compliance.cfm">Certificate of Compliance</a></li>
                    <li><a title="Fees" href="?/building_development/residential_development/fees.cfm">Fees</a></li>
                    <li><a title="Notice of Development" href="?/building_development/planning/notice_of_development/notice_of_development.cfm">Notice of Development</a></li>
                    <li><a title="Street Addresses for New Construction" href="?/gis/index.cfm">Street Addresses for New Construction</a></li>
                  </ul>
                </li>
              </ul>
            </li>
            <li><a href="?/community_safety/index.cfm">Community Safety</a>
              <ul>
                <li><a href="?/disaster_services/index.cfm">Disaster Services</a></li>
                <li><a href="?/emergency_services/index.cfm">Emergency Services</a></li>
                <li><a href="?/municipal_enforcement/index.cfm">Municipal Enforcement</a></li>
                <li><a href="?/rcmp/index.cfm">Royal Canadian Mounted Police</a>
                  <ul>
                    <li><a title="Community Partnership Programs" href="?/rcmp/community_partnership_programs.cfm">Community Partnership Programs</a></li>
                    <li><a title="Traffic Services" href="?/rcmp/traffic_services.cfm">Traffic Services</a></li>
                  </ul>
                </li>
              </ul>
            </li>
            <li><a href="?/community_services/index.cfm">Community Services</a>
              <ul>
                <li><a href="?/directories/community_directory/index.cfm">Community Directory</a></li>
                <li><a href="?/calendars/index.cfm">Community Calendar</a></li>
              </ul>
            </li>
            <li><a href="?/engineering/index.cfm">Engineering Services </a></li>
            <li><a href="?/finance/index.cfm">Finance</a></li>
            <li><a href="?/gis/index.cfm">Maps (GIS)</a></li>
            <li><a href="?/parks/parks_recreation.cfm">Parks &amp; Recreation</a></li>
            <li><a href="?/public_works/index.cfm">Public Works</a></li>
            <li><a href="?/recycling_waste/index.cfm">Recycling, Waste &amp; Composting</a>
              <ul>
                <li><a href="?/environmental_services/index.cfm">Environmental Services </a></li>
              </ul>
            </li>
            <li><a href="?/social_planning/index.cfm">Social Planning</a></li>
            <li><a href="?/taxation/index.cfm">Taxation</a></li>
            <li><a href="?/transit/index.cfm">Transit</a></li>
            <li><a href="?/utilities/index.cfm">Water &amp; Sewer (Utilities)</a></li>
          </ul>
        </li>
        <li><span><strong>News</strong></span>
          <ul>
            <li><a href="?/enewsletters/index.cfm">Airdrie eNewsletters</a>
              <ul>
                <li><a href="?http://www.industrymailout.com/Industry/View.aspx?id=50169&amp;p=679b">Airdrie Today eNewsletter</a></li>
                <li><a href="?http://www.industrymailout.com/Industry/View.aspx?id=47265&amp;q=0&amp;qz=4c4af0">Airdrie @Work eNewsletter</a></li>
                <li><a href="?http://www.industrymailout.com/Industry/Archives.aspx?m=2682&amp;qz=73249dbb">Airdrie eNewsletter Archive</a></li>
              </ul>
            </li>
            <li><a href="?/calendars/index.cfm">Community Calendar</a></li>
            <li><a href="?/community_news/index.cfm">Community News</a></li>
            <li><a href="?/news_release/index.cfm">News Releases</a> (2007)
              <ul>
                <li><a href="?/news_release/2006/index.cfm" title="2006 News Releases">2006 News Releases</a></li>
                <li><a href="?/news_release/2005/index.cfm" title="2005 News Releases">2005 News Releases</a></li>
                <li><a href="?/news_release/2004/index.cfm" title="2004 News Releases">2004 News Releases</a></li>
              </ul>
            </li>
            <li><a href="?/building_development/planning/notice_of_development/notice_of_development.cfm">Notice of Development </a></li>
            <li><a href="?/photogallery/index.cfm">Photo Gallery</a></li>
            <li><a href="?/public_meetings/index.cfm">Public Meetings</a>
              <ul>
                <li><a href="?/public_meetings/appeals/index.cfm">Appeals</a></li>
                <li><a href="?/public_meetings/open_houses/index.cfm">Open Houses</a></li>
                <li><a href="?/public_meetings/public_hearings/index.cfm">Public Hearings</a></li>
              </ul>
            </li>
            <li><a href="?/publications/index.cfm">Publications</a>
              <ul>
                <li><a href="?/publications/pdf/AirdrieLIFE_fall2006.pdf">Airdrie Life Magazine</a> (16MB, .PDF)</li>
                <li><a href="?/publications/pdf/report_for_2005.pdf">Annual Economic Report</a> (5 MB, .PDF)</li>
                <li><a href="?/publications/pdf/Airdrie%20community%20report%20for%202006_sm.pdf">Annual Community Report</a></li>
              </ul>
            </li>
          </ul>
        </li>
        <li><span><strong>City Council &amp; Administration </strong></span>
          <ul>
            <li><a href="?/election/index.cfm">2007 Election</a>
              <ul>
                <li><a href="?/election/city_council.cfm" title="City Council">City Council</a></li>
                <li><a href="?/election/candidates.cfm" title="Candidates">Candidates</a></li>
                <li><a href="?/election/candidate_information_package.cfm" title="Candidate Information Package">Candidate Information Package</a></li>
                <li><a href="?/election/faq.cfm" title="Frequently Asked Questions">Frequently Asked Questions</a></li>
                <li><a href="?/election/how_to_vote.cfm" title="How to Vote">How to Vote</a></li>
                <li><a href="?/election/media.cfm" title="Media">Media</a></li>
                <li><a href="?/election/past_elections.cfm" title="Past Elections">Past Elections</a></li>
              </ul>
            </li>
            <li><a href="?/finance/budget_at_a_glance.cfm">Budget</a></li>
            <li><a href="?/city_council/bylaws/index.cfm">Bylaws</a>
              <ul>
                <li><a href="?/city_council/bylaws/how_bylaws_are_passed.cfm">How Bylaws Are Passed</a></li>
                <li><a href="?/city_council/bylaws/new_laws.cfm">New Laws</a></li>
                <li><a href="?/city_council/policies.cfm">Policies</a></li>
              </ul>
            </li>
            <li><a href="?/economic_development/census/index.cfm">Census</a></li>
            <li><a href="?/city_council/index.cfm">City Council</a></li>
          </ul>
          <ul>
            <li><a href="?/city_council/board_appointments.cfm">Board Appointments</a></li>
            <li><a href="?/city_council/committees_boards_commission.cfm">Committees / Boards / Commssion</a>
              <ul>
                <li><a href="?/city_council/library_board.cfm" title="Airdrie Municipal Library Board">Airdrie Municipal Library Board</a></li>
                <li><a href="?/city_council/assessment_review_board.cfm" title="Assessment Review Board">Assessment Review Board</a></li>
                <li><a href="?/city_council/community_service_advisory_board.cfm" title="Community Services Advisory Board">Community Services Advisory Board</a></li>
                <li><a href="?/city_council/enviromental_advisory_board.cfm" title="Environmental Advisory Board">Environmental Advisory Board</a></li>
                <li><a href="?/city_council/finance_committee.cfm" title="Finance Advisory Committee">Finance Advisory Committee</a></li>
                <li><a href="?/city_council/municipal_planning_commission.cfm" title="Municipal Planning Commission">Municipal Planning Commission</a></li>
                <li><a href="?/city_council/municipal_police_committee.cfm" title="Municipal Police Committee">Municipal Police Committee</a></li>
                <li><a href="?/city_council/subdivision_development_appeal_board.cfm" title="Subdivision and Development Appeal Board">Subdivision and Development Appeal Board</a></li>
              </ul>
            </li>
            <li><a href="?/city_council/faq.cfm">Frequently Asked Questions (FAQ's)</a></li>
            <li><a href="?/city_council/mayors_message.cfm">Mayor's Message</a></li>
            <li><a href="?/city_council/mission_vision.cfm">Mission and Vision</a></li>
            <li><a href="?/city_council/meet_your_council.cfm">Meet Your Council</a></li>
            <li><a href="?/city_council/strategic_priorities.cfm">Strategic Priorities</a>
              <ul>
                <li><a href="?/city_council/strategic_priorities.cfm" title="Strategic Priorities 2008">Strategic Priorities 2008</a></li>
                <li><a href="?/city_council/strategic_priorities_07.cfm" title="Strategic Priorities 2007">Strategic Priorities 2007</a></li>
                <li><a href="?/city_council/strategic_priorities_06.cfm" title="Strategic Priorities 2006">Strategic Priorities 2006</a></li>
                <li><a href="?/city_council/strategic_priorities_05.cfm" title="Strategic Priorities 2005">Strategic Priorities 2005</a></li>
                <li><a href="?/city_council/strategic_priorities_04.cfm" title="Strategic Priorities 2004">Strategic Priorities 2004</a></li>
              </ul>
            </li>
            <li><a href="?/city_council/city_council_meetings.cfm">City Council Meetings</a>
              <ul>
                <li><a href="?/city_council/agendas/2007_agendas.cfm">City Council Meeting Agendas</a>
                  <ul>
                    <li><a href="?/city_council/agendas/2007_agendas.cfm" title="2007 Agendas">2007 City Council Meeting Agendas</a></li>
                    <li><a href="?/city_council/agendas/2006_agendas.cfm" title="2006 Agendas">2006 City Council Meeting Agendas</a></li>
                    <li><a href="?/city_council/agendas/2005_agendas.cfm" title="2005 Agendas">2005 City Council Meeting Agendas</a></li>
                    <li><a href="?/city_council/agendas/2004_agendas.cfm" title="2004 Agendas">2004 City Council Meeting Agendas</a></li>
                  </ul>
                </li>
                <li><a href="?/city_council/minutes/2007_minutes.cfm">City Council Meeting Minutes</a>
                  <ul>
                    <li><a href="?/city_council/minutes/2007_minutes.cfm" title="2007 City Council Meeting Minutes">2007 City Council Meeting Minutes</a></li>
                    <li><a href="?/city_council/minutes/2006_minutes.cfm" title="2006 City Council Meeting Minutes">2006 City Council Meeting Minutes</a></li>
                    <li><a href="?/city_council/minutes/2005_minutes.cfm" title="2005 City Council Meeting Minutes">2005 City Council Meeting Minutes</a></li>
                    <li><a href="?/city_council/minutes/2004_minutes.cfm" title="2004 City Council Meeting Minutes">2004 City Council Meeting Minutes</a></li>
                  </ul>
                </li>
                <li><a href="?/city_council/synopsis/2007_synopsis.cfm">City Council Meeting Synopsis</a>
                  <ul>
                    <li><a href="?/city_council/synopsis/2007_synopsis.cfm" title="2007 City Council Meeting Synopsis">2007 City Council Meeting Synopsis</a></li>
                    <li><a href="?/city_council/synopsis/2006_synopsis.cfm" title="2006 City Council Meeting Synopsis">2006 City Council Meeting Synopsis</a></li>
                    <li><a href="?/city_council/synopsis/2005_synopsis.cfm" title="2005 City Council Meeting Synopsis">2005 City Council Meeting Synopsis</a></li>
                    <li><a href="?/city_council/synopsis/2004_synopsis.cfm" title="2004 City Council Meeting Synopsis">2004 City Council Meeting Synopsis</a></li>
                  </ul>
                </li>
                <li><a href="?/city_council/how_to_go_to_council.cfm">How to Go to Council</a></li>
              </ul>
            </li>
            <li><a href="?/city_council/foip.cfm">FOIP</a></li>
            <li><a href="?/city_council/how_government_works.cfm">How Government Works</a></li>
            <li><a href="?/city_council/legislative_admin_services.cfm">Legislative &amp; Admin Services</a>
              <ul>
                <li><a href="?/city_council/city_managers_message.cfm">City Manager's Message</a></li>
              </ul>
            </li>
            <li><a href="?/org_chart/index.cfm">Organizational Chart</a></li>
          </ul>
        </li>
        <li><strong><a href="?#">Lifestyle</a></strong>
          <ul>
            <li><a href="?/about_airdrie/index.cfm">About Airdrie</a>
              <ul>
                <li><a href="?/about_airdrie/history.cfm">History</a></li>
              </ul>
            </li>
            <li><a href="?/arts_culture/index.cfm">Arts &amp; Culture</a>
              <ul>
                <li><a href="?/arts_culture/airdrie_art.cfm">Airdrie Art</a></li>
                <li><a href="?http://www.airdriepubliclibrary.ca/">Airdrie Public Library</a></li>
                <li><a href="?/arts_culture/airdrie_rodeo_ranch.cfm">Airdrie Rodeo Ranch</a></li>
                <li><a href="?/bert_church_theatre/index.cfm">Bert Church LIVE Theatre</a></li>
                <li><a href="?/twinning_program/index.cfm">Korean Twinning Program</a></li>
                <li><a href="?/arts_culture/little_theatre_association.cfm">Little Theatre Association</a></li>
                <li><a href="?/sport_community_facilities/nose_creek_valley_museum.cfm">Nose Creek Valley Museum</a></li>
                <li><a href="?http://www.rockyview.ab.ca/rvae/">Rocky View Adult Education</a></li>
              </ul>
            </li>
            <li><a href="?/bert_church_theatre/index.cfm">Bert Church LIVE Theatre</a>
              <ul>
                <li><a href="?/bert_church_theatre/about_us.cfm" title="About Us">About Us</a></li>
                <li><a href="?/bert_church_theatre/season_program.cfm" title="Current Season Program">Current Season Program</a></li>
                <li><a href="?/bert_church_theatre/box_office.cfm" title="Box Office">Box Office</a></li>
                <li><a href="?/bert_church_theatre/theatre_rental.cfm" title="Theatre Rental">Theatre Rental</a></li>
                <li><a href="?/bert_church_theatre/technical_specifications.cfm" title="Technical Specifications">Technical Specifications</a></li>
                <li><a href="?/bert_church_theatre/contact_us.cfm" title="Contact Us">Contact Us</a></li>
                <li><a href="?/bert_church_theatre/photogallery.cfm" title="Photo Gallery">Photo Gallery</a></li>
                <li><a href="?/bert_church_theatre/links.cfm" title="Links">Links</a></li>
                <li><a href="?http://www.theresawasden.com/music_in_common.htm" title="Performing Arts Classes">Performing Arts Classes</a></li>
              </ul>
            </li>
            <li><a href="?/elrwc/index.cfm">East Lake Recreation &amp; Wellness Centre</a>
              <ul>
                <li><a href="?/elrwc/about_facility.cfm" title="About the Facility">About the Facility</a></li>
                <li><a href="?/elrwc/contact.cfm" title="Contact Us">Contact Us</a></li>
                <li><a href="?/elrwc/forms.cfm" title="Forms">Forms</a></li>
                <li><a href="?/elrwc/future_phases.cfm" title="Future Phases">Future Phases</a></li>
                <li><a href="?/elrwc/hours_operation.cfm" title="Hours of Operation &amp; Schedules">Hours of Operation &amp; Schedules</a>
                  <ul>
                    <li><a href="?/elrwc/schedules.cfm">Schedules</a></li>
                  </ul>
                </li>
                <li><a href="?/elrwc/city_guide.cfm" title="In the City Guide">In the City Guide</a></li>
                <li><a href="?/elrwc/opportunities_events.cfm" title="Opportunities &amp; Events">Opportunities &amp; Events</a></li>
                <li><a href="?/elrwc/programs_services.cfm" title="Programs &amp; Services">Programs &amp; Services</a>
                  <ul>
                    <li><a title="Aquatics" href="?/elrwc/aquatics.cfm">Aquatics</a>
                      <ul>
                        <li><a title="Water Drop-in Classes" href="?/elrwc/water_classes.cfm">Water Drop-in Classes</a></li>
                      </ul>
                    </li>
                    <li><a title="Child Care Services" href="?/elrwc/child_services.cfm">Child Care Services</a></li>
                    <li><a title="Children Activities" href="?/elrwc/children_activities.cfm">Children Activities</a></li>
                    <li><a title="Fitness &amp; Wellness" href="?/elrwc/fitness_wellness.cfm">Fitness &amp; Wellness</a>
                      <ul>
                        <li><a title="Dry Land Drop-in Classes" href="?/elrwc/land_classes.cfm">Dry Land Drop-in Classes</a></li>
                        <li><a title="Fitness &amp; Wellness Services" href="?/elrwc/fitness_wellness_services.cfm">Fitness &amp; Wellness Services</a></li>
                      </ul>
                    </li>
                    <li><a title="Party Packages" href="?/elrwc/party_packages.cfm">Party Packages</a></li>
                    <li><a title="Room Rentals" href="?/elrwc/room_rentals.cfm">Room Rentals</a></li>
                  </ul>
                </li>
                <li><a href="?/elrwc/rates_fees.cfm" title="Rates &amp; Fees">Rates &amp; Fees</a></li>
                <li><a href="?/elrwc/register_now.cfm" title="Register Now">Register Now</a></li>
              </ul>
            </li>
            <li><a href="?/education/index.cfm">Education</a></li>
            <li><a href="?/health/index.cfm">Health</a></li>
            <li><a href="?/gis/index.cfm">Maps (GIS)</a></li>
            <li><a href="?/parks/parks_recreation.cfm">Parks &amp; Recreation</a></li>
            <li><a title="Parks" href="?/parks/index.cfm">Parks</a>
              <ul>
                <li><a title="City Parks Programs" href="?city_parks_programs.cfm">City Parks Programs</a>
                  <ul>
                    <li><a href="?airdrie_horticulture_society.cfm" title="Airdrie Horticulture Society">Airdrie Horticulture Society</a></li>
                    <li><a href="?communities_in_bloom.cfm" title="Communities in Bloom">Communities in Bloom</a></li>
                    <li><a href="?community_garden.cfm" title="Community Garden">Community Garden</a></li>
                    <li><a href="?landscape_awards_program.cfm" title="Landscape Awards Program">Landscape Awards Program</a></li>
                  </ul>
                </li>
                <li><a title="Maintenance" href="?maintenance.cfm">Maintenance</a>
                  <ul>
                    <li><a href="?dandelions.cfm" title="Dandelions">Dandelions</a></li>
                    <li><a href="?gophers.cfm" title="Gophers">Gophers</a></li>
                    <li><a href="?grass_cutting.cfm" title="Grass Cutting">Grass Cutting</a></li>
                    <li><a href="?pathway_snow_removal.cfm" title="Pathway Snow Removal">Pathway Snow Removal</a></li>
                  </ul>
                </li>
                <li><a title="Maps" href="?/gis/index.cfm">Maps</a></li>
                <li><a title="Outdoor Facilities" href="?outdoor_facilities.cfm">Outdoor Facilities</a>
                  <ul>
                    <li><a title="Ball Diamonds" href="?ball_diamonds.cfm">Ball Diamonds</a></li>
                    <li><a title="BMX Track" href="?bmx_track.cfm">BMX Track</a></li>
                    <li><a title="Bookings" href="?bookings.cfm">Bookings</a></li>
                    <li><a title="Cemetery" href="?cemetery.cfm">Cemetery</a></li>
                    <li><a title="Fire Pits" href="?fire_pits.cfm">Fire Pits</a></li>
                    <li><a title="Gwacheon Park" href="?/twinning_program/index.cfm#gwacheon">Gwacheon Park</a></li>
                    <li><a title="Off-Leash Areas" href="?off_leash_areas.cfm">Off-Leash Areas</a></li>
                    <li><a title="Outdoor Rinks" href="?outdoor_rinks.cfm">Outdoor Rinks</a></li>
                    <li><a title="Parks &amp; Playgrounds" href="?parks_playgrounds.cfm">Parks & Playgrounds</a></li>
                    <li><a title="Skate Park" href="?skate_park.cfm">Skate Park</a></li>
                    <li><a title="Soccer/Athletic Fields" href="?soccer_athletic_fields.cfm">Soccer/Athletic Fields</a></li>
                    <li><a title="Splash Park" href="?splash_park.cfm">Splash Park</a></li>
                    <li><a title="Tennis Courts" href="?tennis_courts.cfm">Tennis Courts</a></li>
                  </ul>
                </li>
                <li><a title="Parks Planning &amp; Construction" href="?parks_planning_construction.cfm">Parks Planning & Construction</a>
                  <ul>
                    <li><a href="?construction.cfm" title="Construction">Construction</a></li>
                    <li><a href="?plans.cfm" title="Plans">Plans</a></li>
                  </ul>
                </li>
                <li><a title="Urban Forest" href="?urban_forest.cfm">Urban Forest</a>
                  <ul>
                    <li><a href="?city_trees.cfm" title="City Trees">City Trees</a></li>
                    <li><a href="?tree_planting.cfm" title="Tree Planting">Tree Planting</a></li>
                  </ul>
                </li>
                <li><a title="Weeds &amp; Pests" href="?weeds_pests.cfm">Weeds & Pests</a>
                  <ul>
                    <li><a href="?mosquito_control.cfm" title="Mosquito Control">Mosquito Control</a></li>
                    <li><a href="?pest_control.cfm" title="Pest Control">Pest Control</a></li>
                    <li><a href="?weed_control_plant_disease.cfm" title="Weed Control &amp; Plant Disease">Weed Control &amp; Plant Disease</a></li>
                  </ul>
                </li>
              </ul>
            </li>
            <li><a title="Sport &amp; Community Facilities" href="?/sport_community_facilities/index.cfm">Sport &amp; Community Facilities</a>
              <ul>
                <li><a title="Indoor Facilities" href="?/sport_community_facilities/indoor_facilities.cfm">Indoor Facilities</a>
                  <ul>
                    <li><a title="Arenas/Gymnastics" href="?/sport_community_facilities/arenas_gymnastics.cfm">Arenas/Gymnastics</a></li>
                    <li><a title="Curling Rink" href="?/sport_community_facilities/curling_rink.cfm">Curling Rink</a></li>
                    <li><a title="East Lake Recreation &amp; Wellness Centre" href="?/elrwc/index.cfm">East Lake Recreation &amp; Wellness Centre</a></li>
                    <li><a title="Nose Creek Valley Museum" href="?/sport_community_facilities/nose_creek_valley_museum.cfm">Nose Creek Valley Museum</a></li>
                    <li><a title="Over 50 Club" href="?/sport_community_facilities/over_50_club.cfm">Over 50 Club</a></li>
                    <li><a title="Town &amp; Country" href="?/sport_community_facilities/town_country.cfm">Town &amp; Country</a></li>
                  </ul>
                </li>
                <li><a title="Outdoor Facilities" href="?/parks/outdoor_facilities.cfm">Outdoor Facilities</a></li>
              </ul>
            </li>
          </ul>
        </li>
        <li><strong><a href="?#">Visiting</a></strong>
          <ul>
            <li><a href="?/economic_development/video/index.cfm">Airdrie LIFE Video</a></li>
            <li><a href="?/gis/recreation_map/index.cfm">Community Map</a></li>
            <li><a href="?/events/index.cfm">Events</a>
              <ul>
                <li><a href="?http://www.airdriefestivaloflights.com">Airdrie Festival of Lights</a></li>
                <li><a href="?http://www.airdrieprorodeo.net/">Airdrie Pro Rodeo</a></li>
                <li><a href="?http://www.pch.gc.ca/special/canada/index_e.cfm">Canada Day</a></li>
              </ul>
            </li>
            <li><a href="?/parks/parks_recreation.cfm">Parks &amp; Recreation</a></li>
            <li><a href="?/economic_development/tourist_information/tourist_information.cfm">Tourist Information</a>
              <ul>
                <li><a href="?/economic_development/entertainment/entertainment.cfm">Entertainment</a></li>
                <li><a href="?/economic_development/hotels/hotels.cfm">Hotels</a></li>
                <li><a href="?/economic_development/restaurants/restaurants.cfm">Restaurants</a></li>
                <li><a href="?/economic_development/shopping/shopping.cfm">Shopping</a></li>
                <li><a href="?http://www1.travelalberta.com/en-ab/index.cfm?country=CA&amp;state=AB&amp;setlocale=1">Travel Alberta</a></li>
              </ul>
            </li>
            <li><a href="?http://www.woodsidegc.com/contact.html">Woodside Golf Course</a></li>
          </ul>
        </li>
        <li><strong><a href="?/economic_development/index.cfm">Doing Business</a></strong>
          <ul>
            <li><a href="?/economic_development/business_attraction/index.cfm">Business Attraction</a>
              <ul>
                <li><a href="?http://www.albertafirst.com/profiles/statspack/20365.html">Airdrie Profile</a></li>
                <li><a href="?/economic_development/business_attraction/business_case.cfm">Business Case For Airdrie</a></li>
                <li><a href="?/economic_development/census/index.cfm">Census Data </a></li>
                <li><a href="?http://www.albertafirst.com/realestate/">Properties and Businesses For Sale</a></li>
                <li><a href="?/taxation/non_residential_comparisons.cfm">Taxation</a></li>
              </ul>
            </li>
            <li><a href="?/economic_development/business_development/index.cfm">Business Development</a>
              <ul>
                <li><a href="?/economic_development/business_development/business_associations.cfm">Business Associations</a></li>
                <li><a href="?/economic_development/business_development/business_resources.cfm">Business Resources</a></li>
                <li><a href="?/economic_development/business_development/business_services.cfm">Business Services</a></li>
                <li><a href="?/corporate_properties/index.cfm">Corporate Properties</a></li>
                <li><a href="?/economic_development/business_development/home_businesses.cfm">Home Based Businesses</a></li>
              </ul>
            </li>
            <li><a href="?/directories/business_directory/index.cfm">Business Directory</a></li>
            <li><a href="?/economic_development/business_licenses/index.cfm">Business Licenses</a>
              <ul>
                <li><a href="?/economic_development/business_licenses/municipal_licenses_permits.cfm">Municipal Licenses &amp; Permits</a></li>
                <li><a href="?/economic_development/business_licenses/provincial_licenses_permits.cfm">Provincial Licenses &amp; Permits</a></li>
                <li><a href="?/economic_development/business_licenses/registry_services.cfm">Registry Services</a></li>
              </ul>
            </li>
            <li><a href="?http://bsa.canadabusiness.ca/gol/bsa/site.nsf/en/index.html">How to Start a Business</a></li>
            <li><a href="?/finance/procurement_services.cfm">Procurement Services</a></li>
          </ul>
        </li>
        <li><strong><a href="?https://vch.airdrie.ca/index.cfm">Online Services</a></strong></li>
      </ul>        
     <!-- <p>Quisque vel felis ligula. Cras viverra sapien auctor ante porta a tincidunt quam pulvinar. Nunc facilisis, enim id volutpat sodales, leo ipsum accumsan diam, eu adipiscing risus nisi eu quam. Ut in tortor vitae elit condimentum posuere vel et erat. Duis at fringilla dolor. Vivamus sem tellus, porttitor non imperdiet et, rutrum id nisl. Nunc semper facilisis porta. Curabitur ornare metus nec sapien molestie in mattis lorem ullamcorper. Ut congue, purus ac suscipit suscipit, magna diam sodales metus, tincidunt imperdiet diam odio non diam. Ut mollis lobortis vulputate. Nam tortor tortor, dictum sit amet porttitor sit amet, faucibus eu sem. Curabitur aliquam nisl sed est semper a fringilla velit porta. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vestibulum in sapien id nulla volutpat sodales ac bibendum magna. Cras sollicitudin, massa at sodales sodales, lacus tortor vestibulum massa, eu consequat dui nulla et ipsum.</p> <p>Quisque vel felis ligula. Cras viverra sapien auctor ante porta a tincidunt quam pulvinar. Nunc facilisis, enim id volutpat sodales, leo ipsum accumsan diam, eu adipiscing risus nisi eu quam. Ut in tortor vitae elit condimentum posuere vel et erat. Duis at fringilla dolor. Vivamus sem tellus, porttitor non imperdiet et, rutrum id nisl. Nunc semper facilisis porta. Curabitur ornare metus nec sapien molestie in mattis lorem ullamcorper. Ut congue, purus ac suscipit suscipit, magna diam sodales metus, tincidunt imperdiet diam odio non diam. Ut mollis lobortis vulputate. Nam tortor tortor, dictum sit amet porttitor sit amet, faucibus eu sem. Curabitur aliquam nisl sed est semper a fringilla velit porta. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vestibulum in sapien id nulla volutpat sodales ac bibendum magna. Cras sollicitudin, massa at sodales sodales, lacus tortor vestibulum massa, eu consequat dui nulla et ipsum.</p>-->
	
      
    
      
      
    </div>
  </div>
  <div class="rightpane">
  	
    
  
   </div>
  
  <!-- EDIT END
-----------------------------------------------------------------------------------> 
</div>
