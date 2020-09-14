<?php 
if (!isset($buttons)) {
    $buttons = '/template/detail-buttons';
}

$buttons = $this->userinfo['rtheme'] . $buttons;
?>
<?php 
if ($this->input->post("rec_from")){
  print '<div class="form-submit-btn">            
      <div class="or-cancel">
          <a class="cancel" href="javascript:void(0)" rel="action-back">Go Back</a>
      </div>
  </div>';
}
?>  
<div class="wizard-leftcol">
  <ul>
    <?php
            if (isset($fieldgroups) && sizeof($fieldgroups) > 0) :
                $load_jqgrid_in_boxy = false;
                $load_ckeditor = false;
                $load_multiselect = false;
                $load_uploadify = false;
                $js = array();
                $ctr = 1;
                foreach ($fieldgroups as $fieldgroup) :
                    ?>
    <li style="width:20%"> <a class="leftcol-control" rel="fg-<?php echo $fieldgroup['fieldgroup_id'] ?>" href="javascript:void(0)"><span class="wizard-ctr"><?php echo $ctr++; ?></span><br />
      <span class="wizard-label" style="width:90%"><?php echo $fieldgroup['fieldgroup_label']; ?></span></a> </li>
    <?php
                endforeach;
            endif;
            ?>
  </ul>
</div>
<!-- content alert messages -->
<div id="message-container">
  <?php
        if (isset($msg)) {
            echo is_array($msg) ? implode("\n", $msg) : $msg;
        }
        if (isset($flashdata)) {
            echo $flashdata;
        }
        ?>
</div>
<!-- content alert messages --> 

<!-- PLACE YOUR MAIN CONTENT HERE -->
<?php if (isset($error)) : ?>
<div id="message_box" class="attention" style="padding-left: 60px;width:60%;margin: 0 auto;"> <img src="<?= base_url() . $this->userinfo['theme']; ?>/images/exclamation-big.png" alt="" >
  <h3 style="margin: 0.3em 0 0.5em 0">Oops!
    <?= $error ?>
  </h3>
  <p>
    <?= $error2 ?>
  </p>
</div>
<? else : ?>
<div class="wizard-rightcol">
  <div class="wizard-header" style="padding:0 0 15px 0">
        <div class="icon-label-group align-left">
          <?php echo $this->load->view($buttons);
            if( $mrf_id == "" ){
              if( $application_status == 1 || $application_status == 5 || $application_status == 8){
          ?>
                  <div class="icon-label">
                      <a class="icon-16-approve" href="javascript:void(0);" onclick="qualify_candidates( <?php echo $position_id; ?> , <?php echo $position2_id; ?> )">
                          <span>Qualified</span>
                      </a>
                  </div>

                  <div class="icon-label">
                      <a class="icon-16-disapprove" href="javascript:void(0);" onclick="disqualify_candidates()">
                          <span>Not Qualified</span>
                      </a>
                  </div>
          <?php     }             ?>
          <div class="icon-label"> <a href="javascript:void(0);" class="icon-16-listback bak-applicant" rel="back-to-list"> <span>Back to list</span> </a> </div>
          <?php } ?>
        </div>    
        <div class="align-right"><h2><span id="fglabel_span">Specify the Job Title</span></h2></div>
  </div>
  <form class="style2 detail-view" name="record-form" id="record-form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="record_id" id="record_id" value="<?php echo isset( $duplicate ) ? '-1' : $this->input->post('record_id') ?>" />
    <input type="hidden" name="return_record_id" id="return_record_id" value="<?= $this->input->post('record_id') ?>" />
    <input type="hidden" name="previous_page" id="previous_page" value="<?= base_url() . $this->module_link ?>/detail"/>
    <input type="hidden" name="prev_search_str" id="prev_search_str" value="<?= $this->input->post('prev_search_str') ?>"/>
    <input type="hidden" name="prev_search_field" id="prev_search_field" value="<?= $this->input->post('prev_search_field') ?>"/>
    <input type="hidden" name="prev_search_option" id="prev_search_option" value="<?= $this->input->post('prev_search_option') ?>"/>
    <input type="hidden" name="from_cs" id="from_cs" value="<?=$this->input->post('from_cs')?>"/>
    <input type="hidden" name="mrf_from_posted_jobs" id="mrf_from_posted_jobs" value="<?=$this->input->post('mrf_from_posted_jobs')?>"/>
    <?php
                if( $mrf_id != "" ){ ?>
                <input type="hidden" name="mrf_id" id="mrf_id" value="<?= $mrf_id ?>"/>
                <input type="hidden" name="candidate_id" id="candidate_id" value="<?= $candidate_id ?>"/>
                <?php }

                if (isset($fieldgroups) && sizeof($fieldgroups) > 0) :
                    foreach ($fieldgroups as $fieldgroup) :
                        ?>
    <div fg_id="<?php echo $fieldgroup['fieldgroup_id'] ?>" id="fg-<?php echo $fieldgroup['fieldgroup_id'] ?>" class="<?php echo $show_wizard_control ? 'wizard-type-form hidden' : '' ?>">
    
      <div class="page-navigator align-right">
        <!-- div class="btn-prev-disabled"> <a href="javascript:void(0)"><span>Prev</span></a></div>
        <div class="btn-prev hidden"> <a onclick="prev_wizard()" href="javascript:void(0)" class="tipsy-autons" ><span>Prev</span></a></div>
        <div class="btn-next"> <a onclick="next_wizard()" href="javascript:void(0)" class="tipsy-autons" ><span>Next</span></a></div>
        <div class="btn-next-disabled hidden"> <a href="javascript:void(0)"><span>Next</span></a></div -->
      </div>
      <!-- div class="icon-label-group align-left"> 
        <?php echo $this->load->view($buttons);?>
        <div class="icon-label"> <a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list"> <span>Back to list</span> </a> </div>
      </div -->
      
      	
      
     	<div class="wizard-form">
      		<div class="<?= !empty($fieldgroup['layout']) ? 'col-1-form' : 'col-2-form' ?> view">
     
        <?php
            if ($fieldgroup['detail_customview'] != "" && $fieldgroup['detail_customview_position'] == 1) {
                $this->load->view($this->userinfo['rtheme'] . '/' . $fieldgroup['detail_customview']);
            }
            if (isset($fieldgroup['fields'])) :
                foreach ($fieldgroup['fields'] as $field) :
                    $this->uitype_detail->showFieldDetail($field);
                endforeach;
            endif;
            if ($fieldgroup['detail_customview'] != "" && $fieldgroup['detail_customview_position'] == 3) {
                $this->load->view($this->userinfo['rtheme'] . '/' . $fieldgroup['detail_customview']);
            }
            if ($fieldgroup['fieldgroup_label'] == 'HRA use'){
        ?>
            <div style="clear:both">&nbsp;</div>
            <h3>Manpower Served (For HR Used Only)</h3>            
            <table id="listview-list" class="default-table boxtype" style="width:100%">
              <thead>
                <tr>
                  <td>Name(s)</td>
                  <td>Date Hired</td>
                  <td>Source of Awareness</td>
                  <td>Salary</td>
                </tr>
              </thead>  
              <tbody>
                <?php
                  if ($manpower_served && $manpower_served->num_rows() > 0){
                    foreach ($manpower_served->result() as $row) {
                ?>
                      <tr>
                        <td><?php echo $row->firstname ?>&nbsp;<?php echo $row->lastname ?></td>
                        <td><?php echo $row->hired_date ?></td>
                        <td><?php echo $row->referred_by ?></td>
                        <td><?php echo number_format($starting_salary,2, '.', ',') ?></td>
                      </tr>   
                <?php
                    }
                  }
                ?>
              </tbody>
            </table>        
        <?php
            }
                        ?>
      </div>
      </div>
    </div>
    <?php
                endforeach;
            endif;

            if (sizeof($views) > 0) :
                foreach ($views as $view) :
                    $this->load->view($this->userinfo['rtheme'] . '/' . $view);
                endforeach;
            endif;
                ?>
  </form>
  <div class="clear"></div>
  <div class="page-navigator align-right">
    <div class="btn-prev-disabled"> <a href="javascript:void(0)"><span>Prev</span></a></div>
    <div class="btn-prev hidden"> <a onclick="prev_wizard()" href="javascript:void(0)" class="tipsy-autons" ><span>Prev</span></a></div>
    <div class="btn-next"> <a onclick="next_wizard()" href="javascript:void(0)" class="tipsy-autons" ><span>Next</span></a></div>
    <div class="btn-next-disabled hidden"> <a href="javascript:void(0)"><span>Next</span></a></div>
  </div>
  <div class="icon-label-group align-left">
    <?php echo $this->load->view($buttons);
      if( $mrf_id == "" ){
        if( $application_status == 1 || $application_status == 5 || $application_status == 8){
    ?>
            <div class="icon-label">
                <a class="icon-16-approve" href="javascript:void(0);" onclick="qualify_candidates( <?php echo $position_id; ?> , <?php echo $position2_id; ?> )">
                    <span>Qualified</span>
                </a>
            </div>

            <div class="icon-label">
                <a class="icon-16-disapprove" href="javascript:void(0);" onclick="disqualify_candidates()">
                    <span>Not Qualified</span>
                </a>
            </div>
    <?php     }             ?>
    <div class="icon-label"> <a href="javascript:void(0);" class="icon-16-listback bak-applicant" rel="back-to-list"> <span>Back to list</span> </a> </div>
    <?php } ?>
  </div>
</div>
<?php endif; ?>
<!-- END MAIN CONTENT -->

</div>
