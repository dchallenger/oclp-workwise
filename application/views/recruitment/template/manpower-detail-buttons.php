
<?php 

$record = $this->db->get_where($this->module_table,array('request_id'=>$this->input->post('record_id')))->row();

$approvers_per_position = $this->system->get_approvers_and_condition($record->requested_by, $this->module_id);

if( $status == 'For Approval'): ?>

    <?php if ($can_approve): ?>
    <div class="icon-label">
        <a class="icon-16-approve" href="javascript:void(0);" onclick="approve()">
            <span>Approve</span>
        </a>
    </div>
    <?php endif; ?>

    <?php if ($can_decline): ?>
    <div class="icon-label">
        <a class="icon-16-disapprove" href="javascript:void(0);" onclick="decline()">
            <span>Disapprove</span>
        </a>
    </div>
    <?php endif; ?>

    <?php if ($can_decline): ?>
   <div class="icon-label" style="display:none">
        <a class="icon-16-disapprove" href="javascript:void(0);" onclick="for_evaluation()">
            <span>For Evaluation</span>
        </a>
    </div> 
    <?php endif; ?>


<?php elseif( $status == 'For HR Review'): ?>

    <?php 

        foreach( $approvers_per_position as $approver ){

            $user = $this->hdicore->_get_userinfo( $approver['approver'] );
            $user_access = $this->hdicore->_create_user_access_file( $user );

            if( $user_access[$this->module_id]['post'] == 1 && $approver['approver'] == $this->userinfo['user_id'] ){ // HR Approver

                ?>

                    <div class="icon-label">
                        <a class="icon-16-tick" href="javascript:void(0);" onclick="mark_as_review()">
                            <span>Mark as Reviewed</span>
                        </a>
                    </div>

                   <!--  <div class="icon-label">
                        <a class="icon-16-disapprove" href="javascript:void(0);" onclick="for_evaluation()">
                            <span>For Evaluation</span>
                        </a>
                    </div> -->

                <?php
            }
        }

     ?>



<?php else: ?>

<?php endif; ?>
