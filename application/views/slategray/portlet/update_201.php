<?php
  $update_p = $this->portlet->get_update_personal($this->user->user_id);

  $update_s = $this->portlet->get_update_subordinates($this->user->user_id, $this->userinfo['position_id']);

?>

<div id="<?php echo $portlet_file?>">

  <ul>
    <li><a href="#forms-personal">Personal</a></li>
    <?php if($update_s != 0) { ?>
      <li><a href="#forms-approval">For Approval
        <span class="ctr-inline bg-orange" id="no_approval_display"><?= count($update_s); ?></span>
        <input type="hidden" id="no_approval" value="<?php echo $update_s; ?>" />
      </a></li>
    <?php } ?>
  </ul>

  <div id="forms-personal">
    <p>
      <small>Your personal application made recently.</small>
    </p>

    <div style="margin:0px 5px;">
      Update 201 Forms
        <?php 
          $limiter=0;

          if(count($update_p)>=5)
            echo '<div style="height:200px;overflow-y: scroll;overflow-x: hidden;padding-right:10px;">';

          if( count($update_p)==0 )
            echo '<p><small>None as of this moment.</small></p>';
          else 
          {
            echo '<ul>';

          foreach ($update_p as $app): 
            if($limiter<30) 
            {
        ?>
          <li>
            <small> filed on </small>
            <a href="<?php echo base_url().'employee/employee_update/detail/'.$app->employee_update_id; ?>">
              <?php echo date('M-j',strtotime($app->date_created));?>
            </a>
            <?php if( $app->employee_update_status_id == 2 ) { ?>
            <span class="align-right orange">
              <small>Approved</small>
            </span>
            <?php } elseif( $app->employee_update_status_id == 1 ) { ?>
            <span class="align-right green">
              <small>Pending</small>
            </span>
            <?php } elseif( $app->employee_update_status_id == 3 ) { ?>
            <span class="align-right red">
              <small>Disapproved</small>
            </span>
            <?php }?>
          </li>
        <?php 
            } 
            $limiter++;
            endforeach; 
            echo '</ul>';
          }
        ?>
        <a style="float:right;font-size:10px;" href="<?php echo base_url().'employee/employee_update/'; ?>">  
          (View All Update 201 Forms) 
        </a>

      <div class="clear"></div>
      <?php 
          if(count($update_p)>=5)
            echo '</div>';
      ?>
    </div>

  </div>


  <?php 
    if($update_s != 0):
  ?>

    <div id="forms-approval">
      <p>
        <small> Update 201 made by your staff. </small>
      </p>

      <div style="margin:0px 5px;">
          Update 201 Forms
        <?php 
          $limiter=0;

          if(count($update_s)>=7)
            echo '<div style="height:200px;overflow-y: scroll;overflow-x: hidden;padding-right:10px;">';

          if(count($update_s)==0)
            echo '<p><small>None as of this moment.</small></p>';
          else 
          {
            echo '<ul>';
          
            foreach ($update_s as $app): 
              if($limiter<30) 
              {

        ?>
              <li>
                <span class="red">
                  <a href="<?php echo base_url().'employee/employee_update/detail/'.$app->employee_update_id; ?>">
                    <?php echo ucfirst($app->firstname) . " " . ucfirst(substr($app->middlename,0,1)) . ($app->middlename <> "" ? ". " : " ") . ucfirst($app->lastname) ;?>
                  </a>
                </span> 
                  <br />

                <small> filed on </small> 
                  <?php 
                    echo date('M-j',strtotime($app->date_created));
                  ?>

                <span class="icon-group align-right">
                  <a class="icon-button icon-16-approve" href="javascript:void(0);" onclick="change_status(1,'<?php echo $app->employee_update_id; ?>')" tooltip="Approve">
                    Approve
                  </a>
                  <a class="icon-button icon-16-disapprove" href="javascript:void(0);" onclick="change_status(2,'<?php echo $app->employee_update_id; ?>')" tooltip="Disapprove">
                    Disapprove
                  </a>
                </span> 
                  <br />

              </li>

          <?php 
             } 
            $limiter++;
            endforeach; 
            echo '</ul>';
          }

          if(count($update_s)>=4)
            echo '</div>';

          if($update_s == 0)
            echo '<p><small>None as of this moment.</small></p>';

          ?>

         <a style="float:right;font-size:10px;" href="<?php echo base_url().'employee/employee_update/'; ?>">  
          (View All Update 201 Forms) 
        </a>

      <div class="clear"></div>
      <?php 
          if(count($update_s)>=5)
            echo '</div>';
      ?>
    </div>

  <?php 
    endif;
  ?>        
</div>

<div class="spacer"></div>
<script type="text/javascript">
  $(document).ready(function() {
    $('#<?php echo $portlet_file?>').tabs();
  });

  function change_status(form_status, id)
  {
    var url = module.get_value('base_url') + 'employee/employee_update/change_status';
    var status;

    switch(form_status)
    {
      case 1:
        status = 'approve';
        break;
      case 2:
        status = 'decline';
        break;
    }

     $.ajax({
            url: url,
            data: 'record_id=' + id + '&status=' + status + '&bypass=1',
            type: 'post',
            dataType: 'json',
            success: function(response) {

              message_growl(response.msg_type, response.msg);

              if( response.type != 'error' ) 
                $('#link_'+id).parent().remove();

               var no_approval = $('#no_approval').val();

               no_approval -= 1;

               if( no_approval == 0 ) {

                $('#approval_list').append('<p><small>None as of this moment.</small></p>');
                $('#no_approval').val(no_approval);
                $('#no_approval_display').remove();

               } else {

                $('#no_approval').val(no_approval);
                $('#no_approval_display').text(no_approval);

               }

               refresh_portlet( 7, 'update_201' );
            }
    });
    
  }


</script>
