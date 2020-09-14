<table id="module-participant" style="width:100%;" class="default-table boxtype">
    <colgroup width="15%"></colgroup>
    <thead>
	    <tr>
	        <th style="vertical-align:middle;" class="odd">
	        	Start Date
	        </th>
	        <th style="vertical-align:middle;" class="odd">
	        	Start Time
	        </th>
	        <th style="vertical-align:middle;" class="odd">
	        	End Time
	        </th>
	        <th style="vertical-align:middle;" class="odd">
	        	Instructor
	        </th>
	    </tr>
    </thead>
    <tbody>
    	<?php

        if( $calendar_session_details_count > 0 ){

            foreach( $calendar_session_details as $calendar_session_details_info ){ 

        ?>
            <tr>
                <td style="text-align:center;"><?= date($this->config->item('display_date_format'),strtotime($calendar_session_details_info->session_date)); ?></td>
                <td style="text-align:center;"><?= date('h:i a',strtotime($calendar_session_details_info->sessiontime_from)); ?></td>
                <td style="text-align:center;"><?= date('h:i a',strtotime($calendar_session_details_info->sessiontime_to)); ?></td>
                <td style="text-align:center;">
                <?php 

                    $session_instructor = explode(',',$calendar_session_details_info->instructor);

                    foreach( $instructor_list as $instructor_info ){
                        if( in_array($instructor_info->training_instructor_id, $session_instructor) ){
                            echo '&bull; '.$instructor_info->training_instructor.'<br />';
                        }
                    } 
                ?>
                </td>
            </tr>

        <?php

            }
        }else{ ?>
            <tr><td colspan="4" style="text-align:center;">No Training Session is Set</td></tr>
        <?php } ?>
    </tbody>
</table>