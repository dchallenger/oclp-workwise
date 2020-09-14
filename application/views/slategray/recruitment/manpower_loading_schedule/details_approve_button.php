   <?php  
        $view_click = '';    
        $approver = $this->db->get_where('manpower_loading_schedule_approver', array('approver' => $this->user->user_id, 'focus' => 1, 'status' => 2, 'mls_id' => $_POST['record_id']));
            if ($approver && $approver->num_rows() == 1) {
                $view_click = 'approver';                
            }
   ?>

        <div class="form-submit-btn">
            <div class="icon-label-group">
               <?php
                if( $view_click == 'approver')
                {
                ?>
                    <div class="icon-label">
                        <a class="icon-16-approve approve-class approve-single" href="javascript:void(0)">
                            <span>Approve</span>
                        </a>
                    </div>
                    <div class="icon-label">
                        <a class="icon-16-disapprove disapprove-class decline-single-detail" href="javascript:void(0)">
                            <span>Disapprove</span>
                        </a>
                    </div>
                <?php
                }
                   ?>
                <div class="icon-label">
                    <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);">
                        <span>Back to list</span>
                    </a>
                </div>
            </div>
        </div>

