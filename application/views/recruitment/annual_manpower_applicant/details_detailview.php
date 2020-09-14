<style type="text/css" media="screen">
	.text-left{ text-align: left !important}
    .rotate { height: 60px; vertical-align: bottom; cursor: pointer;  -moz-user-select: none; -webkit-user-select: none; }
    tbody tr th.module-name { cursor: pointer; -moz-user-select: none; -webkit-user-select: none; }
    .rotate div { -moz-transform: rotate(290deg); -webkit-transform: rotate(290deg); filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3.1); display: block; width: 16px; text-align: center; margin: 0 auto;}
</style>
<h3 class="form-head">Planning Details</h3>
<div class="clear"></div>
<div class="spacer"></div>

<?php

        $annual_manpower_planning = $this->db->dbprefix('annual_manpower_planning');
        $annual_manpower_planning_id = $_POST['record_id'];
        $annual_manpower_planning_header = $this->db->query("SELECT * FROM {$annual_manpower_planning}  WHERE annual_manpower_planning_id = '{$annual_manpower_planning_id}'")->row();

        $this->db->join('user','user.employee_id = annual_manpower_planning_evaluation_remarks.remarked_by','left');
        $this->db->where('annual_manpower_planning_evaluation_remarks.annual_manpower_planning_id',$annual_manpower_planning_id);
        $this->db->group_by('annual_manpower_planning_evaluation_remarks.remarked_by');
        $this->db->order_by('annual_manpower_planning_evaluation_remarks.date_remarked','ASC');
        $annual_manpower_planning_evaluation_remarks = $this->db->get('annual_manpower_planning_evaluation_remarks');

        $view_click = '';
        $re_evaluate = '';
        $annual_status_id = '';
        if($_POST['record_id'] != -1)
        {
            $annual_status_id = $annual_manpower_planning_header->annual_manpower_planning_status_id;
            if($annual_manpower_planning_header->employee_id == $this->userinfo['user_id'])
            {
                $view_click = 'editor';
            }
            if( ( $annual_manpower_planning_header->annual_user_division_id == $this->userinfo['user_id'] || $this->user_access[$this->module_id]['post'] == 1 ) && $annual_manpower_planning_header->annual_manpower_planning_status_id == 2)
            {
                $view_click = 'approver'; 
                $re_evaluate = 1;  
            }
            if($annual_manpower_planning_header->annual_manpower_planning_status_id == 6 && $this->user_access[$this->module_id]['post'] == 1 ){

                $view_click = 'reviewer'; 
                $re_evaluate = 1;  

            }
        }


        $list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");       
        $remarks = $this->db->get('annual_manpower_planning_remarks')->result();

        $this->db->select('annual_manpower_planning_details.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name,jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,`dec`',false);
        $this->db->join('user','user.user_id = annual_manpower_planning_details.user_id');              
        $this->db->join('user_company_department','user.department_id = user_company_department.department_id');
        $this->db->join('user_position','user.position_id = user_position.position_id');
        $this->db->join('employee','employee.user_id = user.user_id');
        $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id');
        $this->db->where('annual_manpower_planning_details.annual_manpower_planning_id',$annual_manpower_planning_id);
        $this->db->order_by('user_rank.rank_index','DESC');
        $this->db->group_by('position_id');
        $position = $this->db->get('annual_manpower_planning_details');


?>

<?php

    if( $annual_status_id == 4 && $annual_manpower_planning_evaluation_remarks->num_rows() > 0 ){

        foreach( $annual_manpower_planning_evaluation_remarks->result() as $remarks_info ){

?>
    <div class="col-2-form view"> 
        <div class="form-item view odd ">
            <label class="label-desc view gray" for="remarks">Remarked By:</label>         
            <div class="text-input-wrap"><?php echo $remarks_info->firstname." ".$remarks_info->lastname; ?></div>
        </div>
        <div class="form-item view odd ">
            <label class="label-desc view gray" for="remarks">Date:</label>         
            <div class="text-input-wrap"><?= date('F d, Y',strtotime($remarks_info->date_remarked) ); ?></div>
        </div>                        
        <div class="form-item view odd ">
            <label class="label-desc view gray" for="remarks">Remarks:</label>         
            <div class="text-input-wrap"><?= $remarks_info->remarks ?></div>
        </div>
    </div>
    <br />
    
<?php

        } ?>


<p><span style="font-weight:bold;">Note:</span> All highlighted records needs to be evaluated</p><br />

<?php

    }
?>

        <table id="module-access" style="width:100%" class="default-table boxtype">
            <colgroup width="15%"></colgroup>
            <thead>
                <tr>
                    <th style="vertical-align:middle">Employee / Position</th>
                        <!-- Display header -->
                        <?php foreach( $list_month as $index => $month ){ ?><th style="vertical-align:middle" class="action-name font-smaller <?php echo ($index % 2 == 0 ? "even" : "odd"); ?>"><div><?php echo ($month); ?></div></th><?php } ?>
                    <th class="action-name font-smaller even"><?php if( $re_evaluate == 1 ){ ?>Evaluate<br /><input type="checkbox" class="reevaluate_incumbent_all" name="" /><?php } ?></th>
                </tr>
            </thead>
            <tbody class="structure_list">
            <?php 

            foreach($position->result() as $position_row){

                    $this->db->select('user.user_id, employee.employed_date, employment_status.employment_status,annual_manpower_planning_details.annual_manpower_planning_details_id, annual_manpower_planning_details.disapproved, annual_manpower_planning_details.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name,jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,`dec`',false);
                    $this->db->join('user','user.user_id = annual_manpower_planning_details.user_id'); 
                    $this->db->join('employee','employee.user_id = user.user_id','left');
                    $this->db->join('employment_status','employment_status.employment_status_id = employee.status_id','left');             
                    $this->db->join('user_company_department','user.department_id = user_company_department.department_id');
                    $this->db->join('user_position','user.position_id = user_position.position_id');
                    $this->db->where('annual_manpower_planning_details.annual_manpower_planning_id',$annual_manpower_planning_id);
                    $this->db->where('user_position.position_id',$position_row->position_id);
                    $this->db->order_by('annual_manpower_planning_details_id','ASC');
                    $user = $this->db->get('annual_manpower_planning_details');

                    $incumbent_count = $user->num_rows();

            ?>
                <tr>
                    <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="14">
                        <span>
                            <span><?php echo $position_row->position; ?> ( <?= $incumbent_count; ?> )</span>
                        </span>
                    </th>
                </tr>

                <?php

                    foreach($user->result() as $user_row){

                        $tooltip = '<table>
                            <tr>
                                <td style=\'text-align:right; font-weight:bold;\'>Employment Status</td>
                                <td> : </td>
                                <td style=\'text-align:left;\'>'.$user_row->employment_status.'</td>
                            </tr>
                            <tr>
                                <td style=\'text-align:right; font-weight:bold;\'>Hired Date</td>
                                <td> : </td>
                                <td style=\'text-align:left;\'>'.date('F d, Y',strtotime($user_row->employed_date)).'</td>
                            </tr>
                        </table>';

                ?>

                        <tr id="<?php echo $user_row->user_id; ?>" class="<?php echo ($ctr % 2 == 0 ? "even" : "odd"); ?>">
                            <input type="hidden" name="user_id[]" value="<?php echo $user_row->user_id; ?>">
                            <input type="hidden" name="position_id[]" value="<?php echo $user_row->position_id; ?>">

                            <th style="border-top: none;" class="text-left">
                                <ul type="disc" style="font-size:11px; padding-left:20px;">
                                    <li><a href="javascript:void(0)" tooltip="<?= $tooltip; ?>"><span <?php if( $user_row->disapproved == 1 ){ ?> class="red" <?php } ?> >&bull; <?= $user_row->name; ?></span></a></li>
                                </ul>
                            </th>
                            <?php
                            foreach( $list_month as $index => $month){

                                $monthsmall = strtolower($month);
                                $remarks_value = "";
                                foreach( $remarks as $remarks_info ){
                                    if( $remarks_info->annual_manpower_planning_remarks_id == $user_row->$monthsmall ){
                                        $remarks_value = $remarks_info->remarks;
                                    }
                                }
                            ?>
                                <td axis="<?php echo strtolower($month); ?>" style="vertical-align:middle; text-align:center;" class="text-center <?php echo ($index % 2 == 0 ? "even" : "odd"); ?> ">
                                    <?php echo $remarks_value; ?>
                                </td>
                           <?php } ?>
                           <td  class="text-center even " style="vertical-align:middle; text-align:center;"><?php if( $re_evaluate == 1 ){ ?><input type="checkbox" class="reevaluate_incumbent" value="<?php echo $user_row->annual_manpower_planning_details_id; ?>" name="incumbent_reevaluate[]" /><?php } ?></td>
                        </tr>

                 <?php   }  

                $ctr++; 
            }

            ?>



       </tbody>
        </table>
        <div class="spacer"></div>


        <?php

            $list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
            $total = array();
            $existing_position = $this->db->get_where('annual_manpower_planning_position', array('annual_manpower_planning_id' => $annual_manpower_planning_id, 'type' => 2, 'deleted' => 0 ) );
            $new_position = $this->db->get_where('annual_manpower_planning_position', array('annual_manpower_planning_id' => $annual_manpower_planning_id, 'type' => 1, 'deleted' => 0 ) );
            $display = "";


            if( $existing_position->num_rows == 0 && $new_position->num_rows == 0 ){

                $display = "display:none;";

            }


            ?>

            <table id="module-headcount" style="width:100%; <?php echo $display; ?>" class="default-table boxtype">
            <colgroup width="15%"></colgroup>
            <thead>
                <tr>
                    <th style="vertical-align:middle;" class="text-left" colspan="16">
                        <span>
                            <span>Existing Job</span>
                        </span>
                    </th>
                </tr>
                <tr>
                    <th style="vertical-align:middle"></th>
                    <th class="even" style="vertical-align:middle"><span>Previous AMP</span></th>
                        <!-- Display header -->
                        <?php foreach ( $list_month as $index => $month ){ ?><th style="vertical-align:middle" class="action-name font-smaller <?php echo ($index % 2 == 0 ? "even" : "odd"); ?>"><div><?php echo ($month); ?></div></th><?php } ?>
                <th class="even" style="vertical-align:middle"><span>Total</span></th>
                <th class="odd"><?php if( $re_evaluate == 1 ){ ?><span>Evaluate</span><br /><input class="reevaluate_existing_headcount_all" type="checkbox" name="" /><?php } ?></th>
                </tr>
            </thead>
            <tbody class="existing_job_headcount">
                <?php 
                if( $existing_position->num_rows() > 0 ){

                    $existing_position_list = $existing_position->result_array();

                    foreach( $position_hierarchy as $position_hierarchy_record ){

                        foreach( $existing_position_list as $key => $val ){

                            if( $position_hierarchy_record['position_id'] == $existing_position_list[$key]['position_id'] ){

                            $sub_total = 0;

                            $previous_amp = 0;

                            if( $year ){

                                $annual_year = $year - 1;

                                $this->db->join('annual_manpower_planning','annual_manpower_planning.annual_manpower_planning_id = annual_manpower_planning_position.annual_manpower_planning_id','left');
                                $this->db->where('annual_manpower_planning_position.position_id',$position_hierarchy_record['position_id']);
                                $this->db->where('annual_manpower_planning_position.type',2);
                                $this->db->where('annual_manpower_planning.year',$annual_year);
                                $this->db->where('annual_manpower_planning.department_id',$department_id);
                                $this->db->where('annual_manpower_planning.annual_manpower_planning_status_id',3);
                                $previous_amp_result = $this->db->get('annual_manpower_planning_position');
                                
                                if( $previous_amp_result->num_rows() > 0 ){

                                    $previous_amp_record = $previous_amp_result->row_array();

                                    $previous_amp = $previous_amp_record['total'];

                                }

                            }


                    ?>
                        <tbody class="existing_job_form">
                            <tr>
                                <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="15">
                                    <span <?php if( $existing_position_list[$key]['disapproved'] == 1 ){ ?> class="red" <?php } ?> ><?php echo $position_hierarchy_record['position']; ?></span>
                                </th>
                                <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-center even"><?php if( $re_evaluate == 1 ){ ?><input type="checkbox" class="reevaluate_existing_headcount" value="<?php echo $existing_position_list[$key]['annual_manpower_planning_position_id']; ?>" name="existing_headcount_reevaluate[]" /><?php } ?></th>
                            </tr>
                            <tr>
                                        <th style="border-top: none; text-align:left;">Headcount</th>
                                        <td style="border-top: none; text-align:center;"><?php echo $previous_amp; ?></td>
                    <?php 

                                        foreach( $list_month as $index => $month){
                                            $monthsmall = strtolower($month);
                                            $sub_total = $sub_total + $existing_position_list[$key][strtolower($month)];
                                            $total[$month] += $existing_position_list[$key][strtolower($month)];

                    ?>
                                            <td axis="<?php echo strtolower($month); ?>" style="vertical-align:middle; text-align:center;" class="text-center <?php echo ($index % 2 == 0 ? "even" : "odd"); ?> ">
                                                    <label><?php echo $existing_position_list[$key][strtolower($month)]; ?></label>
                                                </td>
                                     <?php   } 

                                            $total['grand_total'] += $sub_total;

                                     ?>
                                     <td style="vertical-align:middle; text-align:center;" class="text-center even ">
                                                    <label><?php echo $sub_total; ?></label>
                                                </td>
                                                <td style="vertical-align:middle; text-align:center;" class="text-center odd"></td>
                                            </tr>
                                </tbody>
                                     <?php

                            }

                        }

                    }

                }
                else{
                    ?>
                    <tr>
                    <td style="text-align:center;" class="no_existing_job_found" colspan="14">
                        <span style="text-align:center;">No Existing Job Found</span>
                    </td>
                </tr>
               <?php } ?>

                </tbody>
            </table>












                <br />
                <table id="module-headcount" style="width:100%; <?php echo $display; ?>" class="default-table boxtype">
                <colgroup width="15%"></colgroup>
                <thead>
                    <tr>
                        <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="15">
                            <span>
                                <span>New Job</span>
                            </span>
                        </th>
                    </tr>
                    <tr>
                        <th style="vertical-align:middle"></th>
                            <!-- Display header -->
                            <?php foreach ( $list_month as $index => $month ){ ?><th class="action-name font-smaller <?php echo ($index % 2 == 0 ? "even" : "odd"); ?>"><div><?php echo ($month); ?></div></th><?php } ?>
                    <th class="even"><span>Total</span></th>
                    <th class="odd"><?php if( $re_evaluate == 1 ){ ?><span>Evaluate</span><br /><input type="checkbox" class="reevaluate_new_headcount_all" name="" /><?php } ?></th>
                </tr>
                </thead>
                <tbody class="new_job_headcount">
                <?php
                
                if( $new_position->num_rows() > 0 ){

                    $new_position_list = $new_position->result_array();

                    foreach( $new_position_list as $key => $val ){

                         $sub_total = 0;
                    ?>
                        <tr>
                            <th class="text-left even" style="vertical-align:middle; border-top: none; padding: 10px;" colspan="14">
                                <span <?php if( $new_position_list[$key]['disapproved'] == 1 ){ ?> class="red" <?php } ?> ><?php echo $new_position_list[$key]['position']; ?></span>
                            </th>
                            <th class="text-center even" style="vertical-align:middle; border-top: none; padding: 10px;">
                                <?php if( $re_evaluate == 1 ){ ?><input type="checkbox" class="reevaluate_new_headcount" value="<?php echo $new_position_list[$key]['annual_manpower_planning_position_id']; ?>" name="new_headcount_reevaluate[]" /><?php } ?>
                            </th>
                        </tr>

                        <tr class="new_job_form">
                            <th style="border-top: none; text-align:left;">Headcount</th>
                    <?php
                            foreach( $list_month as $index => $month){
                                $monthsmall = strtolower($month);
                                $sub_total = $sub_total + $new_position_list[$key][strtolower($month)];
                                $total[$month] += $new_position_list[$key][strtolower($month)];
                    ?>
                                <td axis="<?php echo strtolower($month); ?>" style="vertical-align:middle; text-align:center;" class="text-center <?php echo ($index % 2 == 0 ? "even" : "odd"); ?> ">
                                        <label><?php echo $new_position_list[$key][strtolower($month)]; ?></label>
                                    </td>
                    <?php
                            } 

                            $total['grand_total'] += $sub_total;

                            ?>

                            <td style="vertical-align:middle; text-align:center;" class="text-center even ">
                                                <label><?php echo $sub_total; ?></label>
                                            </td>
                            <td style="vertical-align:middle; text-align:center;" class="text-center even "></td>
                        </tr>
                        <tr>
                            <th style="border-top: none; text-align:left;">Remarks</th>
                            <td style="vertical-align:middle; text-align:left;" class="text-center even " colspan="14"><?php echo $new_position_list[$key]['remarks']; ?></td>
                        </tr>
                    <?php 
                    }

                }
                else{
                    ?>
                    <tr>
                    <td style="text-align:center;" class="no_new_job_found" colspan="15">
                        <span style="text-align:center;">No New Job Found</span>
                    </td>
                </tr>

    <?php  } 

    ?>

                

</tbody>
</table>
<?php /* Temporary hide grand total of existing and new job
<table id="module-total" style="width:100%; <?php echo $display; ?>" class="default-table boxtype">
<tbody>
    <tr>
        <th style="border-top: none; text-align:center;">Total</th>

        <?php
                foreach( $list_month as $index => $month){
        ?>
                    <td style="vertical-align:middle; text-align:center;" class="text-center <?php echo ($index % 2 == 0 ? "even" : "odd"); ?> ">
                            <label><?php echo $total[$month]; ?></label>
                        </td>
                    <?php   } ?>
        <td style="vertical-align:middle; text-align:center;"><?php echo $total['grand_total']; ?></td>
    </tr>
</tbody>
        </table>
        <div class="spacer"></div>



*/ ?>






<?php /*
<table id="module-access" style="width:100%" class="default-table boxtype">
    <colgroup width="15%"></colgroup><?php
    //list month
    $list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"); ?>

    <thead>
        <tr>
            <th style="vertical-align:middle">Employee / Position</th>
            <?php
                // Display the Actions
                foreach ( $list_month as $index => $month ) echo '<th class="action-name font-smaller '.($index % 2 == 0 ? "even" : "odd").'"><div>'.($month).'</div></th>'; ?>
                <th class="action-name font-smaller <?php echo ($index % 2 == 0 ? "even" : "odd") ?>"><div>Total</div></th>
        </tr>
    </thead>
    <tbody><?php
        //get list of modules
        $remarks = $this->db->get('annual_manpower_planning_remarks');
        $remarks_array = $remarks->result_array();
        $this->db->select('annual_manpower_planning_details.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name,jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,`dec`',false);
        $this->db->join('user','user.user_id = annual_manpower_planning_details.user_id');              
        $this->db->join('user_company_department','user.department_id = user_company_department.department_id');
        $this->db->join('user_position','user.position_id = user_position.position_id');
        $this->db->where('annual_manpower_planning_details.annual_manpower_planning_id',$annual_manpower_planning_id);
        $this->db->order_by('annual_manpower_planning_details_id','ASC');
        $position = $this->db->get('annual_manpower_planning_details');
        $total = 0;
        $monthly_array = array();
        foreach($position->result() as $row): 
            $sub_total = 0; ?>    
            <tr id="<?= $row->user_id ?>" class="<?=($ctr % 2 == 0 ? "even" : "odd")?>">
                <th class="text-left" style="border-top: none"><span><span><?=$row->name?></span><br /><span style="padding-left:10px;float:left">-<?=$row->position?></span></span></th>
                <?php foreach( $list_month as $index => $month):
                    $monthsmall = strtolower($month);
                    $arr_val = explode("||",$row->$monthsmall); 
                    $sub_total += $arr_val[1];
                    $monthly_array[$monthsmall][] = $arr_val[1]; ?>
                    <td class="text-center <?=($index % 2 == 0 ? "even" : "odd")?> " style="vertical-align:middle">
                        <div style="text-align:center"><?php echo $remarks_array[$arr_val[0] - 1]['remarks'] ?></div>
                        <div style="text-align:center"><?php echo $arr_val[1] ?></div>                     
                    </td> 
                <?php endforeach; 
                $total += $sub_total; ?>
                <td class="text-center <?=($index % 2 == 0 ? "even" : "odd")?> " style="vertical-align:middle">
                    <div style="text-align:center">&nbsp;</div>
                    <div style="text-align:center"><?php echo $sub_total ?></div>                     
                </td>                                
            </tr> 
        <?php 
            $ctr++; 
            endforeach; 

            $this->db->select('annual_manpower_planning_positionposition,annual_manpower_planning_details.jan,annual_manpower_planning_details.feb,annual_manpower_planning_details.mar,annual_manpower_planning_details.apr,annual_manpower_planning_details.may,annual_manpower_planning_details.jun,annual_manpower_planning_details.jul,annual_manpower_planning_details.aug,annual_manpower_planning_details.sep,annual_manpower_planning_details.oct,annual_manpower_planning_details.nov,annual_manpower_planning_details.dec',false);
            $this->db->join('annual_manpower_planning','annual_manpower_planning_details.annual_manpower_planning_id = annual_manpower_planning.annual_manpower_planning_id');
            $this->db->join('annual_manpower_planning_position','annual_manpower_planning_details.annual_manpower_planning_position_id = annual_manpower_planning_position.annual_manpower_planning_position_id');              
            $this->db->where('annual_manpower_planning_details.annual_manpower_planning_id',$annual_manpower_planning_id);
            $this->db->order_by('annual_manpower_planning_details_id','ASC');
            $position = $this->db->get('annual_manpower_planning_details');            

            if ($position){
                foreach($position->result() as $row): 
                        $sub_total = 0; ?>
                        <tr id="<?= $row->user_id ?>" class="<?= ($ctr % 2 == 0 ? "even" : "odd") ?>">
                        <th class="text-left" style="border-top: none"><span><span>&nbsp;</span><br /><span style="padding-left:10px;float:left">-<?=$row->position?></span></span></th>
                        <?php foreach( $list_month as $index => $month):
                            $monthsmall = strtolower($month);
                            $arr_val = explode("||",$row->$monthsmall);
                            $sub_total += $arr_val[1]; 
                            $monthly_array[$monthsmall][] = $arr_val[1]; ?>
                            <td class="text-center <?= ($index % 2 == 0 ? "even" : "odd") ?> " style="vertical-align:middle;text-align:center">
                                <span><?php echo ($arr_val[1] != '' ? 'Hire' : '') ?></span>
                                <div style="padding-top:5px"><?php echo $arr_val[1] ?><div>                     
                            </td>
                        <?php endforeach; 
                        $total += $sub_total; ?>
                        <td class="text-center <?=($index % 2 == 0 ? "even" : "odd")?> " style="vertical-align:middle">
                            <div style="text-align:center">&nbsp;</div>
                            <div style="text-align:center"><?php echo $sub_total ?></div>                     
                        </td>                         
                    </tr> 
                <?php $ctr++; 
                endforeach;
            }            
        ?>
        <tr class="<?=($ctr % 2 == 0 ? "even" : "odd")?>">
            <th class="text-center" style="border-top: none;vertical-align:middle"><span>Total</span></th>
            <?php foreach( $list_month as $index => $month): ?>
                <td class="text-center <?=($index % 2 == 0 ? "even" : "odd")?> " style="vertical-align:middle">
                    <div style="text-align:center">&nbsp;</div>
                    <div style="text-align:center"><?php echo array_sum($monthly_array[strtolower($month)]) ?></div>
                    <div style="text-align:center">&nbsp;</div>
                </td> 
            <?php endforeach; ?>
            <td class="text-center <?=($index % 2 == 0 ? "even" : "odd")?> " style="vertical-align:middle">
                <div style="text-align:center">&nbsp;</div>
                <div style="text-align:center"><?php echo $total ?></div>
                <div style="text-align:center">&nbsp;</div>
            </td>             
        </tr>        
    </tbody>
</table>

*/ ?>
<div class="spacer"></div>