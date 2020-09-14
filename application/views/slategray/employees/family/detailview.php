<script>
$(document).ready(function () {
// alert(check_status(module.get_value('record_id')));
  if(user.get_value('post_control')==1)
    check_status(module.get_value('record_id'), true);
  if(user.get_value('post_control')!=1)
    check_status(module.get_value('record_id'), false);
 
     $('.icon-16-approve').live('click', function () {
       change_status(module.get_value('record_id'), 'approve');
       //location.reload();
     });

     $('.icon-16-disapprove').live('click', function () {
       change_status(module.get_value('record_id'), 'decline');
       //location.reload();
     }); 

    function change_status(id, status) {
          $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/change_status',
            data: 'status=' + status + '&record_id=' + id,
            type: 'post',
            success: function (response) {      
              $('#message-container').html(message_growl(response.msg_type, response.msg));
              $('.icon-label-group').remove();
              $('.or').remove();
              //$("#jqgridcontainer").jqGrid().trigger("reloadGrid");
            }
        });
    }

    function check_status(id, has_post) {
          $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/check_status',
            data: 'record_id=' + id,
            type: 'post',
            success: function (response) {      
              employee = response.data;
              if(has_post) {
                if(employee.employee_update_status_id==1){
                    $('.form-submit-btn').prepend('<div class="icon-label-group" style="margin-left:0px;margin-right:-8px;"><div class="icon-label"><a class="icon-16-disapprove" href="javascript:void(0);" onclick="disapprove()"><span>Decline</span></a></div></div>');
                    $('.form-submit-btn').prepend('<div class="icon-label-group" style="margin-left:-25px;"><div class="icon-label"><a class="icon-16-approve" href="javascript:void(0);" onclick="approve()"><span>Approve</span></a></div></div>');
                    //$('.form-submit-btn').prepend('<div class="icon-label-group"><div class="icon-label"><a class="icon-button icon-16-approve" id="approve" tooltip="Approve" href="javascript:void(0)" original-title=""></a></div></div>');
                } 
                else if(employee.employee_update_status_id>1){
                    $('.icon-label-group').remove();
                    $('.or').remove();
                }
                //alert(employee.employee_update_status_id);
              } else {
                    $('.icon-label-group').remove();
                    $('.or').remove();
              }
               
            }   
        });
    }

    var dom=$.trim($('label[for="personal_dom"]').siblings('.text-input-wrap').text());
    if(dom=="1970-01-01" || dom=="0000-00-00")
      $('label[for="personal_dom"]').siblings('.text-input-wrap').text('');
    else
    {
      var birthdate_w_format=$.datepicker.formatDate('mm/dd/yy',new Date(dom));
      $('label[for="personal_dom"]').siblings('.text-input-wrap').text(birthdate_w_format);
    }
});

    //$('label[for="dummy_position"]').siblings('.text-input-wrap').text('a');
      // show_position(module.get_value('record_id'));

      // function show_position(rec_id)
      // {
      //    var send='record_id='+rec_id;
      //    $.ajax({
      //     url: module.get_value('base_url') + 'employees/show_position',
      //     data: send,
      //     dataType: 'json',
      //     type: 'post',
      //     success: function (response) {
      //         if (response.msg_type == 'error') {
      //             $('#message-container').html(message_growl(response.msg_type, response.msg));
      //         } else {
      //             employee = response.data;   
      //             $('label[for="dummy_position"]').siblings('.text-input-wrap').text('a');
      //             for(var i in employee){
      //               $('.1f input:text').eq(0).val(employee[i].name);
      //               $('.1f input:text').eq(1).val(employee[i].relationship);
      //               $('.1f input:text').eq(2).val(employee[i].birthdate);
      //               $('.1f input:text').eq(3).val(employee[i].occupation);
      //               $('.1f input:text').eq(4).val(employee[i].employer);
      //               $('.1f input:text').eq(5).val(Number(i)+1);
      //               //$('.1f').find('.add-more-div').hide();
      //               //alert(Number(i)+1);
      //               //clone();
      //               //alert(employee[i].name);

      //             }
      //                 //show_name_of_editor(employee[0].employee_id)
      //         }
      //     }
      // });
      // }
</script>

  <form name="record-form" id="record-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="record_id" id="record_id"  value="<?= $this->input->post('record_id') ?>"/>
        <input type="hidden" name="previous_page" id="previous_page" value="<?=base_url().$this->module_link?>"/>
        <input type="hidden" name="prev_search_str" id="prev_search_str" value="<?=$this->input->post('prev_search_str')?>"/>
        <input type="hidden" name="prev_search_field" id="prev_search_field" value="<?=$this->input->post('prev_search_field')?>"/>
        <input type="hidden" name="prev_search_option" id="prev_search_option" value="<?=$this->input->post('prev_search_option')?>"/>
    </form>
<?php
  if($this->input->post('record_id')!=-1){
    $this->db->where('deleted', 0);
    $this->db->where('employee_update_id', $this->input->post('record_id'));
    $pos=$this->db->get('employee_update')->result_array();
    $user = $this->hdicore->_get_userinfo( $pos[0]['employee_id'] );
    echo "<script>$('label[for=\"dummy_position\"]').siblings('.text-input-wrap').text('".$user->position."');</script>";
  }

    $this->db->where('deleted', 0);
    $this->db->where('employee_update_id', $this->input->post('record_id'));
    //$this->db->join('employee_update', 'employee_update.employee_update_id = employee_update_family.employee_update_id', 'left');
    $arr=$this->db->get('employee_update_family')->result_array();
    
    //$('label[for="dummy_position"]').siblings('.text-input-wrap').text();
    if($arr!==0)
    {
      //$keynames = array_keys($arr[0]);
      echo "<input type='hidden' id='total_count_fam_in_detail' value='".count($arr)."' />";
      foreach($arr as $fields=>$fieldval){
?>
<div class="col-2-form view" style="padding-bottom:30px;">
<div class="form-item view odd ">
   <label for="<?= $fieldval['flagcount']; ?>name" class="label-desc view gray fam"> Name </label>
   <div class="text-input-wrap">
      <?= $fieldval['name']; ?>
   </div>        
</div>
<div class="form-item view even ">
   <label for="<?= $fieldval['flagcount']; ?>relationship" class="label-desc view gray fam"> Relationship </label>
   <div class="text-input-wrap">
      <?= $fieldval['relationship']; ?>
   </div>        
</div>
<div class="form-item view odd ">
   <label for="<?= $fieldval['flagcount']; ?>birthdate" class="label-desc view gray fam"> Birthdate </label>
   <div class="text-input-wrap">
      <?php
      if($fieldval['birthdate']=="1970-01-01")
        echo "&nbsp;";
      else
        echo date($this->config->item('edit_date_format'), strtotime($fieldval['birthdate']));
      ?>
   </div>        
</div>
<div class="form-item view even ">
   <label for="<?= $fieldval['flagcount']; ?>occupation" class="label-desc view gray fam"> Occupation </label>
   <div class="text-input-wrap">
      <?php if(strlen($fieldval['occupation'])>35) echo substr($fieldval['occupation'], 0, 35)."<br />".substr($fieldval['occupation'], 35); else echo $fieldval['occupation']; ?>
   </div>        
</div>
<div class="form-item view odd ">
   <label for="<?= $fieldval['flagcount']; ?>employer" class="label-desc view gray fam"> Employer </label>
   <div class="text-input-wrap">
      <?php if(strlen($fieldval['employer'])>35) echo substr($fieldval['employer'], 0, 35)."<br />".substr($fieldval['employer'], 35); else echo $fieldval['employer']; ?>
   </div> 
</div>

<div class="form-item view even ">
   <label for="<?= $fieldval['flagcount']; ?>educational_attainment" class="label-desc view gray fam"> Educational Attainment </label>
   <div class="text-input-wrap">
      <?php if(strlen($fieldval['educational_attainment'])>35) echo substr($fieldval['educational_attainment'], 0, 35)."<br />".substr($fieldval['educational_attainment'], 35); else echo $fieldval['educational_attainment']; ?>
   </div>        
</div>
<div class="form-item view odd ">
   <label for="<?= $fieldval['flagcount']; ?>degree" class="label-desc view gray fam"> Degree </label>
   <div class="text-input-wrap">
      <?php if(strlen($fieldval['degree'])>35) echo substr($fieldval['degree'], 0, 35)."<br />".substr($fieldval['degree'], 35); else echo $fieldval['degree']; ?>
   </div> 
</div>

<div class="form-item view odd ">
   <label for="<?= $fieldval['flagcount']; ?>ecf_dependent" class="label-desc view gray fam"> ECF Dependent </label>
   <div class="text-input-wrap">
      <?= ($fieldval['ecf_dependent'] > 0 ? 'Yes' : 'No'); ?>
   </div> 
</div>

<div class="form-item view even ">
   <label for="<?= $fieldval['flagcount']; ?>bir_dependent" class="label-desc view gray fam"> BIR dependent </label>
   <div class="text-input-wrap">
      <?= ($fieldval['bir_dependent'] > 0 ? 'Yes' : 'No'); ?>
   </div>        
</div>
<div class="form-item view odd ">
   <label for="<?= $fieldval['flagcount']; ?>hospitalization_dependent" class="label-desc view gray fam"> Hospitalization Dependent </label>
   <div class="text-input-wrap">
      <?= ($fieldval['hospitalization_dependent'] > 0 ? 'Yes' : 'No'); ?>
   </div> 
</div>


</div>
<?php 
      } 
    }
?>