<?php 
$record_id = $this->input->post('record_id'); 
if (!isset($buttons)) {
    $buttons = 'template/edit-buttons';
}

$buttons = $this->userinfo['rtheme'] . '/' . $buttons;

if (isset($default_fg)):?>
    <input type="hidden" name="default_fg" value="<?php echo $default_fg?>" />
<?php endif;?>
<input type="hidden" id="client_dir" value="<?=CLIENT_DIR?>">
<input type="hidden" id="status_id" value="<?=$records->status?>">

<?php if ($show_wizard_control):?>
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
<?php endif;?>

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

<?php if ($show_wizard_control):?>
  <div class="wizard-header">
        <div class="align-left"><p><small><?=$fieldgroup['description']?></small></p></div>
        <div class="align-right"><h2><span id="fglabel_span"></span></h2></div>
  </div>
<?php endif;?>

  <form enctype="multipart/form-data" method="post" id="record-form" name="record-form" class="style2 edit-view">
    <input type="hidden" name="record_id" rel="dynamic" id="record_id" value="<?php echo isset( $duplicate ) ? '-1' : $this->input->post('record_id') ?>" />
    <input type="hidden" name="prev_search_str" id="prev_search_str" value="<?= $this->input->post('prev_search_str') ?>"/>
    <input type="hidden" name="prev_search_field" id="prev_search_field" value="<?= $this->input->post('prev_search_field') ?>"/>
    <input type="hidden" name="prev_search_option" id="prev_search_option" value="<?= $this->input->post('prev_search_option') ?>"/>
    <input type="hidden" name="prev_search_page" id="prev_search_page" value="<?= $this->input->post('prev_search_page') ?>"/>
    <?php if( $mrf_id != "" ){ ?>
        <input type="hidden" name="mrf_id" id="mrf_id" value="<?= $mrf_id ?>"/>
        <input type="hidden" name="candidate_id" id="candidate_id" value="<?= $candidate_id ?>"/>
    <?php } ?>
    <input type="hidden" name="from_cs" id="from_cs" value="<?=$this->input->post('from_cs')?>"/>
    <input type="hidden" name="rp" id="rp" value="<?= $rp ?>"/>
    <div id="form-div">
      <?php foreach ($fieldgroups as $fieldgroup) : ?>
      <div fg_id="<?php echo $fieldgroup['fieldgroup_id'] ?>" id="fg-<?php echo $fieldgroup['fieldgroup_id'] ?>" class="<?php echo $show_wizard_control ? 'wizard-type-form hidden' : '' ?>">
        <!-- div class="page-navigator align-right">
          <div class="btn-prev-disabled"> <a href="javascript:void(0)"><span>Prev</span></a></div>
          <div class="btn-prev hidden"> <a onclick="prev_wizard()" href="javascript:void(0)" class="tipsy-autons"><span>Prev</span></a></div>
          <div class="btn-next"> <a onclick="next_wizard()" href="javascript:void(0)" class="tipsy-autons"><span>Next</span></a></div>
          <div class="btn-next-disabled hidden"> <a href="javascript:void(0)"><span>Next</span></a></div>
        </div -->
        <?php //$this->load->view($buttons)?>    
            <div class="icon-label-group align-left">
                <div class="icon-label add-more-div" style="display: none;"><a class="icon-16-add icon-16-add-listview add-more" href="javascript:void(0);" rel="education"><span>Add</span></a></div>
            </div>            
       		<div class="wizard-form">
         			 <div class="<?= !empty($fieldgroup['layout']) ? 'col-1-form' : 'col-2-form' ?>" >                        
          <?php             
                                if ($fieldgroup['edit_customview'] != "" && $fieldgroup['edit_customview_position'] == 1)
                                $this->load->view($this->userinfo['rtheme'] . '/' . $fieldgroup['edit_customview']);
                                if (isset($fieldgroup['fields']) && sizeof($fieldgroup['fields']) > 0) :                            
                                    foreach ($fieldgroup['fields'] as $field) :
                                        //set js validation params
                                        $datatypes = explode('~', $field['datatype']);
                                        $is_mandatory = false;
																				$is_readonly = in_array( 'R', $datatypes )? true : false;
                                        if( $is_readonly ) $js['readonly'][] = $field['fieldname'];
                                        foreach ($datatypes as $datatype) {
                                            if ($datatype == "M") {
                                                $js['mandatory'][] = array($field['fieldname'], $field['fieldlabel']);
                                                $is_mandatory = true;
                                            }
                                            if ($datatype == "I" && !$is_readonly){
                                                if($field['uitype_id'] == 35){
                                                    $js['integer'][] = array($field['fieldname'].'_from', $field['fieldlabel']);
                                                    $js['integer'][] = array($field['fieldname'].'_to', $field['fieldlabel']);
                                                }
                                                else
                                                    $js['integer'][] = array($field['fieldname'], $field['fieldlabel']);
                                            }

                                            if ($datatype == "F" && !$is_readonly){
                                                    if($field['uitype_id'] == 35) {
                                                        $js['float'][] = array($field['fieldname'].'_from', $field['fieldlabel']);
                                                        $js['float'][] = array($field['fieldname'].'_to', $field['fieldlabel']);
                                                    }
                                                    else {
                                                       $js['float'][] = array($field['fieldname'], $field['fieldlabel']);
                                                    }
                                            }
                                            
                                            if ($datatype == "N")
                                                $js['numeric'][] = array($field['fieldname'], $field['fieldlabel']);

                                            if ($datatype == "E")
                                                $js['email'][] = array($field['fieldname'], $field['fieldlabel']);
                                            if ($datatype == "U")
                                                $js['url'][] = array($field['fieldname'], $field['fieldlabel']);
                                            if ($datatype == "P")
                                                $js['password'][] = array($field['fieldname'], $field['fieldlabel']);
                                            if (preg_match("/LE/", $datatype) > 0)
                                                $js['le'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                            if (preg_match("/LT/", $datatype) > 0)
                                                $js['lt'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                            if (preg_match("/GE/", $datatype) > 0)
                                                $js['ge'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                            if (preg_match("/GT/", $datatype) > 0)
                                                $js['gt'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                        }
                                        $this->uitype_edit->showFieldInput($field, $is_mandatory, $fieldgroup['use_tabindex']);
                                        if ($field['uitype_id'] == 5 && !$is_readonly)
                                            $js['date'][] = array($field['fieldname'], $field['fieldlabel']);
                                        if ($field['uitype_id'] == 24 && !$is_readonly)
                                            $js['date_from_to'][] = array($field['fieldname'], $field['fieldlabel']);
                                        if ($field['uitype_id'] == 26 && !$is_readonly)
                                            $js['time_start_end'][] = array($field['fieldname'], $field['fieldlabel']);
                                        if ($field['uitype_id'] == 13)
                                            $load_jqgrid_in_boxy = true;
                                        if ($field['uitype_id'] == 16) {
                                            $js['ckeditor'][] = array($field['fieldname'], $field['fieldlabel']);
                                            $load_ckeditor = true;
                                        }

                                        if ($field['uitype_id'] == 33 && !$is_readonly)
                                            $js['timepicker'][] = array($field['fieldname'], $field['fieldlabel']);
                                        if ($field['uitype_id'] == 38 && !$is_readonly)
                                            $js['time_start_end_picker'][] = array($field['fieldname'], $field['fieldlabel']);
                                        if ($field['uitype_id'] == 37 && !$is_readonly)
                                            $js['min_sec_picker'][] = array($field['fieldname'], $field['fieldlabel']);
                                        if ($field['uitype_id'] == 32 && !$is_readonly)
                                            $js['datetime_picker'][] = array($field['fieldname'], $field['fieldlabel']);
                                        if ($field['uitype_id'] == 40 && !$is_readonly)
                                            $js['datetime_from_to_picker'][] = array($field['fieldname'], $field['fieldlabel']);

                                        if ($field['uitype_id'] == 11) {
                                            $js['single_upload'][] = array($field['fieldname'], $field['fieldlabel']);
                                            $load_uploadify = true;
                                        }
                                        if ($field['uitype_id'] == 20) {
                                            $js['multiple_upload'][] = array($field['fieldname'], $field['fieldlabel'], $field['field_id']);
                                            $load_uploadify = true;
                                        }
                                        if ($field['uitype_id'] == 21) {
                                            $js['multiselect'][] = array($field['fieldname'], $field['fieldlabel']);
                                            $load_multiselect = true;
                                        }
                                        if ($field['uitype_id'] == 39 || $field['uitype_id'] == 28) {
                                            $js['chosen_autocomplete'][] = array($field['fieldname'], $field['fieldlabel']);
                                            $chosen_autocomplete = true;
                                        }                                             
                                    endforeach;
                                    //create js validation
                                    if ($show_wizard_control) {                                        
                                        $js['fg_id'] = $fieldgroup['fieldgroup_id'];                                        
                                        $this->load->view($this->userinfo['rtheme'] . '/template/edit-wizard-form-js', $js, FALSE, FALSE);
                                        $js = array();
                                    }
                                endif;
                                if ($fieldgroup['edit_customview'] != "" && $fieldgroup['edit_customview_position'] == 3)
                                    $this->load->view($this->userinfo['rtheme'] . '/' . $fieldgroup['edit_customview']);
                                ?>
        </div>
        	</div>
        <div class="spacer"></div>
      </div>
      <?php
                    endforeach;
                    //load additional js base on field and validation
                    if ($load_ckeditor)
                        echo CKEditor_script();
                    if ($load_jqgrid_in_boxy)
                        echo jqgrid_in_boxy();
                    if ($load_multiselect)
                        echo multiselect_script();
                    if ($load_uploadify)
                        echo uploadify_script();
                    if ($chosen_autocomplete) 
                        echo chosen_script();

                    //create js validation
                    if (!$show_wizard_control) {
                        $this->load->view($this->userinfo['rtheme'] . '/template/edit-form-js', $js);
                    }
                            ?>
    </div>
  </form>
  <div class="page-navigator align-right">
    <div class="btn-prev-disabled"> <a href="javascript:void(0)"><span>Prev</span></a></div>
    <div class="btn-prev hidden"> <a onclick="prev_wizard()" href="javascript:void(0)" class="tipsy-autons"><span>Prev</span></a></div>
    <div class="btn-next"> <a onclick="next_wizard()" href="javascript:void(0)" class="tipsy-autons"><span>Next</span></a></div>
    <div class="btn-next-disabled hidden"> <a href="javascript:void(0)"><span>Next</span></a></div>
  </div>
  <?php $this->load->view($buttons)?>

  <div class="clear"></div>
  <?php endif; ?>
</div>
<div class="spacer"></div>
</dl>
<script type="text/javascript">
    function validate_fg672() {

        validate_mandatory('date_from', "Date of Program From");
        validate_mandatory('date_to', "Date of Program To");
        validate_mandatory('training_course_id', "Training Course");

        if (process_errors()) {
            var rank_id = $('#rank_id').val();
            var course = $('#training_course_id').val();
            var employee_id = $('#employee_id').val();
            var date_from = $('#date_from').val();
            get_budget(rank_id, course, employee_id, date_from);
            return true
        }else{
            return false;
        };

       
    }
</script>