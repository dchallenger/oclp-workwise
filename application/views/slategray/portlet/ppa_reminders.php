<?php 
  $ppa = $this->portlet->get_ppa();
?>

<div id="<?php echo $portlet_file?>">
  <ul>
    <li><a href="#performance-1" id="tab-2">Performance Planning</a></li>
    <li><a href="#performance-2" id="tab-3">Performance Appraisal</a></li>
    <li><a href="#performance-3" id="tab-4">Employee IDP</a></li>

<?php if($ppa['hideIDP']['visibility']): ?>
    <li><a href="#performance-4" id="tab-5">IDP For Approval
<?php   if($ppa['hideIDP']['count'] > 0): ?>
      <span class="ctr-inline bg-orange" id="for_approval_display"><?php echo $ppa['hideIDP']['count']; ?></span>
      <input type="hidden" id="for_approval" value="<?php echo $ppa['hideIDP']['count'] ?>" />
<?php   endif; ?>
    </a></li>
<?php endif; ?>

  </ul>
<?php 
  $tab_ctr = 1;
  foreach ($ppa as $key => $perfs):
    if($key != "hideIDP"):  ?>
    <div id="performance-<?php echo $tab_ctr++?>">
      <?php if (count($perfs) > 0): ?>
        <table width="100%" border="0" id="portlet-table">
            <tbody>
            <?php  foreach ($perfs as $perf): 

              if($tab_ctr == 4 || $tab_ctr == 5){ 
                $module_site = 'appraisal/individual_development_plan';
              }
              else{
                $module_site = $tab_ctr == 2 ? 'appraisal/appraisal_planning' : 'employee/appraisal'; 
              }?>
            <tr>    
              <td width="60%">
              <?php if($tab_ctr == 4 || $tab_ctr == 5){ ?>
                <strong>
                  <?php $perf_planning_period_link = ($perf['planning_period_id'] == -1) ? '' : '/detail/'.$perf['planning_period_id'];
                  echo $perf['planning_period'] ?>
                </strong>
                <br><small><a tooltip="<?php echo (($perf['planning_period_id'] == -1) ? 'View all record?' : 'View details of this record?'); ?>" class="detail-viewer" recordid="<?php echo $perf['planning_period_id'] ?>"
                      href="<?php echo site_url($module_site.$perf_planning_period_link) ?>"><?php echo (($perf['planning_period_id'] == -1) ? '(View all Individual Development Planning)' : 'Individual Development Planning'); ?></a>
              <?php if($perf['planning_period_id'] > 0): ?>
                Filed On: <?php echo $perf['planning_date_from']  ?></small>
              <?php endif; ?>
              <?php }
              else{ ?>
                <strong>
                <?php if ( $perf['count'] > 0 ) { ?>
                <a tooltip="View details of this record?" href="<?php echo site_url($module_site.'/index/'.$perf['planning_period_id']) ?>">
                  <?php echo $perf['planning_period'] ?>
                </a>
                <?php }
                else { ?>
                    <?php echo $perf['planning_period'] ?>
                <?php } ?>
              </strong>
                  <br><small>Period: <?php echo $perf['planning_date_from'] .' to '. $perf['planning_date_to']  ?></small>
                  <?php if($tab_ctr == 2 || $tab_ctr == 3){ ?>
                  <br><small>Mid Year Assessment : <?php echo $perf['planning_mid_date_from'] .' to '. $perf['planning_mid_date_to']  ?></small>
                  <?php } ?>
              <?php } ?>
              </td>
              <td width="33%" align="right">
                <?php if($tab_ctr == 4 || $tab_ctr == 5){ ?>
                  <?php if($perf['status'] == 'For Approval'){
                        $this->db->where('code', 'idp');
                        $module_idp = $this->db->get('module');

                        if($module_idp && $module_idp->num_rows() > 0){
                          $module_idp_row = $module_idp->row();
                          
                          $this->db->where_in('record_id', $perf['planning_period_id']);
                          $this->db->where_in('approver', $this->user->user_id);
                          $this->db->where('module_id', $module_idp_row->module_id);
                          $approver_user = $this->db->get('employee_appraisal_approver');

                          if ($approver_user && $approver_user->num_rows() > 0) {
                            $approver = $approver_user->row();
                            if ($approver->status == 3) {
                              $_can_approve = true;
                            }
                          }
                        }
                        else{
                          $_can_approve = false;
                        }

                        if ( $_can_approve || $this->is_superadmin) {
                          echo '<a class="icon-button icon-16-approve approve-single"  record_id="'.$perf['planning_period_id'].'" tooltip="Approve" container="jqgridcontainer" module_link="appraisal/individual_development_plan" href="javascript:void(0)"></a>';
                          echo '<a class="icon-button icon-16-disapprove cancel-single" record_id="'.$perf['planning_period_id'].'" tooltip="Decline" container="jqgridcontainer" module_link="appraisal/individual_development_plan" href="javascript:void(0)"></a>';
                        }
                    ?>
                    
                  <?php } ?>
                <?php }
                else{ ?>
                  <a  href="javascript:void(0);" style="float:right;font-size:10px;"><?=($perf['status'] == 1) ? 'Open' : 'Closed' ?></a>
                <?php } ?>
              </td>
            </tr>
          <?php endforeach;?>
             </tbody>
      </table>
      <?php endif; ?>
    </div>
  <?php endif;
  endforeach; ?>

</div>

<script type="text/javascript">
  $(document).ready(function() {
    $('#<?php echo $portlet_file ?>').tabs();

    $('.approve-single').live('click', function() {
        var record_id = $(this).attr('record_id');
        forApproval(record_id, 'Approved');
    });

    $('.cancel-single').live('click', function() {
        var record_id = $(this).attr('record_id');
        forApproval(record_id, 'Decline');
    });
  });

  function forApproval(record_id, status) {
    remarks_boxy = new Boxy.confirm(
      '<div id="boxyhtml" ><textarea style="height:100px;width:340px;" name="remarks_approver"></textarea></div>',
      function () {
          remarks = $('textarea[name="remarks_approver"]').val();
          setTimeout(
            change_status_idp(record_id, status, remarks)
            ,100);
      },
      {
          title: 'Remarks',
          draggable: false,
          modal: true,
          center: true,
          unloadOnHide: true,
          beforeUnload: function (){
              $('.tipsy').remove();
          }
      });
  }


  function change_status_idp(record_id, status, remarks)
  {
    $.ajax({
        url: module.get_value('base_url') + 'appraisal/individual_development_plan/change_status',
        data: 'record_id=' + record_id + '&status=' + status + '&remarks_approver=' + remarks,
        type: 'post',
        dataType: 'json',
        beforeSend: function(){
              show_saving_blockui();
        },
        success: function (data) {
            $.unblockUI({ onUnblock: function() { $('#message-container').html(message_growl(data.msg_type, data.msg)) } });

            window.location = module.get_value('base_url') + module.get_value('module_link');
        }
    });
  }
</script>

