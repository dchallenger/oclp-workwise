<style type="text/css" media="screen">
    .text-left{ text-align: left !important}
    .rotate { height: 60px; vertical-align: bottom; cursor: pointer;  -moz-user-select: none; -webkit-user-select: none; }
    tbody tr th.module-name { cursor: pointer; -moz-user-select: none; -webkit-user-select: none; }
    .rotate div { -moz-transform: rotate(290deg); -webkit-transform: rotate(290deg); filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3.1); display: block; width: 16px; text-align: center; margin: 0 auto;}
</style>
<?php 
    $annual_manpower_planning = $this->db->dbprefix('annual_manpower_planning');
    $annual_manpower_planning_id = $_POST['record_id'];
    $annual_manpower_planning_header = $this->db->query("SELECT * FROM {$annual_manpower_planning}  WHERE annual_manpower_planning_id = '{$annual_manpower_planning_id}'")->row();
    
    $this->db->join('user','user.employee_id = annual_manpower_planning_evaluation_remarks.remarked_by','left');
    $this->db->where('annual_manpower_planning_evaluation_remarks.annual_manpower_planning_id',$annual_manpower_planning_id);
    $this->db->group_by('annual_manpower_planning_evaluation_remarks.remarked_by');
    $this->db->order_by('annual_manpower_planning_evaluation_remarks.date_remarked','ASC');
    $annual_manpower_planning_evaluation_remarks = $this->db->get('annual_manpower_planning_evaluation_remarks');

    $new_position = $this->db->get_where('annual_manpower_planning_position', array('annual_manpower_planning_id' => $annual_manpower_planning_id, 'type' => 1, 'deleted' => 0 ) );
    
    $this->db->join('user_position','user_position.position_id = annual_manpower_planning_position.position_id');
    $existing_position = $this->db->get_where('annual_manpower_planning_position', array('annual_manpower_planning_id' => $annual_manpower_planning_id, 'type' => 2, 'annual_manpower_planning_position.deleted' => 0 ) );

    // planning_details
    $this->db->select('annual_manpower_planning_details.user_id,user_rank.job_rank, employee.rank_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name,jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,`dec`',false);
    $this->db->join('user','user.user_id = annual_manpower_planning_details.user_id');              
    $this->db->join('user_company_department','user.department_id = user_company_department.department_id');
    $this->db->join('user_position','user.position_id = user_position.position_id');
    $this->db->join('employee','employee.user_id = user.user_id');
    $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id');
    $this->db->where('annual_manpower_planning_details.annual_manpower_planning_id',$annual_manpower_planning_id);
    $this->db->order_by('user_rank.rank_index','DESC');
    $this->db->group_by('position_id');
    $position_with_incumbent = $this->db->get('annual_manpower_planning_details');
    // planning_details


    $annual_status_id = $annual_manpower_planning_header->annual_manpower_planning_status_id;        
    $with_incumbent = false;
    $existing = false;

    if ($position_with_incumbent && $position_with_incumbent->num_rows() > 0) {
       $with_incumbent = true;

    }

    if ($existing_position && $existing_position->num_rows() > 0) {
       $existing = true;

    }

    $this->db->where('deleted',0);
    $this->db->order_by('annual_manpower_planning_remarks.sequence','asc');     
    $remarks = $this->db->get('annual_manpower_planning_remarks');
    $list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
  
?>
<h3 class="form-head">Planning Details</h3>

<!-- <p class="form-group-description align-left">Check all that applies. You can also click the <strong><em>action name</em></strong> or the <strong><em>module name</em></strong> to check the column or the rows respectively.</p> -->

<div class="clear"></div>
<div class="spacer"></div>
<div id="module-access-container">
<input type="hidden" id="with_incumbent" value="<?=$with_incumbent?>">
<input type="hidden" id="existing" value="<?=$existing?>">

    <table class="default-table boxtype" style="width:100%" id="module-access">
            <colgroup width="15%"></colgroup>
            <thead>
                <tr class="">
                    <th style="text-align:left;" colspan="15">Positions with Incumbent</th>
                </tr>
                <tr class="">
                    <th style="vertical-align:middle">Employees</th>
                    <th class="action-name font-smaller odd"><div>Rank</div></th>
                    <?php foreach ( $list_month as $i => $month ):?>
                    <th class="action-name font-smaller <?=($i % 2 == 0 ? "even" : "odd")?>"><div><?=($month)?></div></th>
                    <?php endforeach;?>
                    <th class="action-name font-smaller even"><div>Budget</div></th>
                    
                </tr>
            </thead>
            <tbody class="structure_list">
                <?php   $ctr = 1;
                    $incumbent_arr = array();
                    if ($with_incumbent):
                       foreach($position_with_incumbent->result() as $position_row):
                        $this->db->select('employee.employed_date, employment_status.employment_status,user_rank.job_rank, employee.rank_id,annual_manpower_planning_details.annual_manpower_planning_details_id, annual_manpower_planning_details.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname, " ", middleinitial) name,jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,`dec`, annual_manpower_planning_details.budget',false);
                            $this->db->join('user','user.user_id = annual_manpower_planning_details.user_id','left');
                            $this->db->join('employee','employee.user_id = user.user_id','left');           
                            $this->db->join('employment_status','employment_status.employment_status_id = employee.status_id','left');             
                            $this->db->join('user_company_department','user.department_id = user_company_department.department_id');
                            $this->db->join('user_position','user.position_id = user_position.position_id');
                            $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id','left');
                            $this->db->where('annual_manpower_planning_details.annual_manpower_planning_id', $annual_manpower_planning_id);
                            $this->db->where('user_position.position_id',$position_row->position_id);
                            $this->db->order_by('annual_manpower_planning_details_id','ASC');
                            $user = $this->db->get('annual_manpower_planning_details');

                            $incumbent_count = $user->num_rows();
                            $incumbent_arr[$position_row->position_id] = $user->num_rows();
                ?>
                <tr>
                    <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="15">
                        <span>
                            <span><?=$position_row->position.' ( '.$incumbent_count.' )' ?></span>
                        </span>
                    </th>
                </tr>
                <?php  
                        foreach($user->result() as $user_row): 
                        $tooltip = "<table><tr><td>Employment Status</td><td> : ".$user_row->employment_status."</td><td></td></tr>
                        <tr><td>Hired Date</td><td> : ".date('F d, Y',strtotime($user_row->employed_date))."</td><td></td></tr></table>";
                ?>
                <tr id="<?=$user_row->user_id?>" class="<?=($ctr % 2 == 0 ? "even" : "odd")?> position_with_incumbent">
                <input type="hidden" name="user_id[<?=$user_row->annual_manpower_planning_details_id?>]" value="<?=$user_row->user_id?>">
                <input type="hidden" name="position_id[<?=$user_row->annual_manpower_planning_details_id?>]" value="<?=$user_row->position_id?>">

                <th style="border-top: none;" class="text-left">
                    <ul type="disc" style="font-size:11px; padding-left:20px;">
                        <li><a href="javascript:void(0)" tooltip="<?=$tooltip?>">&bull; <?=$user_row->name?></a></li>
                    </ul>
                </th>
                <td><input type='hidden' name='rank_id[<?=$user_row->annual_manpower_planning_details_id?>]' value='<?=$user_row->rank_id?>'>
                                <input type='text' readonly='readonly' value='<?=$user_row->job_rank?>' ></td>
                <?php  foreach( $list_month as $index => $month){
                            $monthsmall = strtolower($month); 
                            
                            ?>
                <td axis="<?=strtolower($month)?>" style="vertical-align:middle; text-align:center;" class="text-center <?=($index % 2 == 0 ? "even" : "odd")?> ">
                    <select style="width:60px" name="remarks_<?=strtolower($month)?>[<?=$user_row->annual_manpower_planning_details_id?>]" class="manpower_setup">
                        <option value="">Select</option>
                        <?php 
                            foreach ($remarks->result() as $row_remarks):?>
                        <option value="<?=$row_remarks->annual_manpower_planning_remarks_id?>" <?=($user_row->{$monthsmall} == $row_remarks->annual_manpower_planning_remarks_id ? "SELECTED" : "")?> ><?=$row_remarks->remarks?></option>
                        <?php  endforeach;?>
                    </select>
                </td>
                <?php   }
                ?>
                <td><input type='text' name='budget[<?=$user_row->annual_manpower_planning_details_id?>]' class="budget" value='<?=$user_row->budget?>' style='width:60px'></td>
                
               <?php    endforeach;   ?>      

                <?php   
                        endforeach;
                    $ctr++; 
                    endif;
                ?>
            </tbody>
        </table>
</div>
<br />

<div class="clear"></div>
<div class="spacer"></div>
<div id="headcount-container"></div>


<!-- new table -->
<div class="clear"></div>
<div class="spacer"></div>
<div id="headcount-existing-container">
    <table id="module-exist-headcount" style="width:100%" class="default-table boxtype">
                    <colgroup width="15%"></colgroup>
                    <thead>
                        <tr class="">
                            <th style="text-align:left;" colspan="2" >Existing Job</th>
                            <th style="text-align:center;" colspan="15">To Hire</th>
                        </tr>
                        <tr class="">
                            <th style="vertical-align:middle"><small>&nbsp;</small></th>
                            <th class="action-name font-smaller even"><div>Incumbent</div></th>
                            <?php foreach ( $list_month as $k => $month ):?>
                            <th class="action-name font-smaller <?=($k % 2 == 0 ? "even" : "odd")?>"><div><?=($month)?></div></th>
                            <?php endforeach;?>

                             <th class="action-name font-smaller even"><span>Approved HC</span></th>
                            <th class="action-name font-smaller odd"><span>Budget</span></th>
                            
                            <th class="action-name font-smaller odd"><span><small>&nbsp;</small></span></th>
                        </tr>
                    </thead>
                        <tbody>
                            <?php  if ($existing): 
                                    foreach ($existing_position->result_array() as $key => $existing_pos):?>
                              <tr>
                                    <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="17"><?=$existing_pos['position']?>
                                    <input type="hidden" name="existing_position[<?=$existing_pos['annual_manpower_planning_position_id']?>]" class="existing_position_id" value="<?=$existing_pos['position_id']?>" />
                                    </th>
                                </tr>
                                <tr>
                                    <th style="border-top:none;">Headcount
                                        <span style="display: inline-block; vertical-align: middle;">
                                            <a style="background-color:transparent;border: 1px solid transparent" atitle="Rank Details"  class="icon-button icon-16-info rank_details " href="javascript:void(0)" original-title="" amp-position-id="<?=$existing_pos['annual_manpower_planning_position_id']?>" position="<?=$existing_pos['position_id']?>" ></a>
                                        </span>
                                    </th>
                                    <td style="text-align:center">
                                        <input type="text" style="width:30px" readonly="" class="existing_job_headcount_previous" name="existing_job_headcount_previous[]" value="<?=($incumbent_arr[$existing_pos['position_id']]) ? $incumbent_arr[$existing_pos['position_id']] : 0 ;?>" /></td>
                                
                                <?php foreach ( $list_month as $m => $month ){ ?>

                                    <td style="text-align:center"><input type="text" style="width:30px" class="existing_headcount_month_value" value="<?=$existing_pos[strtolower($month)]?>" name="existing_job_headcount_<?=strtolower($month)?>[<?=$existing_pos['annual_manpower_planning_position_id']?>]" /></td>

                               <?php } ?>
                                   <td style="text-align:center"><input type="text" style="width:30px" value="<?=$existing_pos['total']?>" readonly="" class="existing_headcount_month_total" name="existing_job_headcount_total[<?=$existing_pos['annual_manpower_planning_position_id']?>]" /></td>
                                  <td style="text-align:center"><input type="text" style="width:60px" class="budget" name="existing_job_budget[<?=$existing_pos['annual_manpower_planning_position_id']?>]" value="<?=$existing_pos['budget']?>" /></td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                </tr>   
                            <?php   endforeach;?>
                                
                            <?php endif;?>
                        </tbody>
                </table>
</div>

<div class="clear"></div>
<div class="spacer"></div>
<div id="headcount-new-container">
    <table id="module-new-headcount" style="width:100%" class="default-table boxtype">
        <colgroup width="15%"></colgroup>
        <thead>
            <tr class="">
                <th colspan="17" style="text-align:left;">New Job</th>
            </tr>
            <tr class="">
                <th style="vertical-align:middle">&nbsp;</th>
                <?php foreach ( $list_month as $j => $month ):?>
                    <th class="action-name font-smaller <?=($j % 2 == 0 ? "even" : "odd")?>"><div><?=($month)?></div></th>
                <?php endforeach;?>
                <th class="action-name font-smaller even"><span>Total</span></th>
                <th class="action-name font-smaller odd"><span>Budget</span></th>
              <!-- -->  <th class="action-name font-smaller even"><div></div></th>
                <th class="action-name font-smaller odd"><span>&nbsp;</span></th> 
            </tr>
        </thead>
        <?php if( $new_position->num_rows() > 0 ){ ?>
        
        <tbody class="new_job_headcount">
        <?php $new_position_list = $new_position->result_array();
             
            foreach( $new_position_list as $key => $val ){

                 $sub_total = 0;
                ?>
            <tr>
                <th style="vertical-align:middle; text-align:center; border-top: none; padding: 10px;" class="text-left even">Position:<span class="red font-large">*</span></th>
                <th class="text-left even" style="vertical-align:middle; border-top: none; padding: 10px;" colspan="14">
                    <input type="text" class="new_headcount_position" name="new_position_name[<?=$new_position_list[$key]['annual_manpower_planning_position_id'];?>]" value="<?=$new_position_list[$key]['position'];?>"/></th>
                <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="2"><a class="icon-button icon-16-delete delete-single delete_new_headcount_position" href="javascript:void(0)" module_link="recruitment/annual_manpower_planning" container="jqgridcontainer" tooltip="Delete"></a></th>
            </tr>

            <tr class="new_job_form">
                <th style="border-top: none; text-align:center;">Headcount

                    <span style="display: inline-block; vertical-align: middle;">
                        <a style="background-color:transparent;border: 1px solid transparent" atitle="Rank Details"  class="icon-button icon-16-info rank_details " href="javascript:void(0)" original-title="" amp-position-id="<?=$new_position_list[$key]['annual_manpower_planning_position_id'];?>"  position="<?=$new_position_list[$key]['position'];?>" ></a>
                        
                    </span></th>
                    <?php
                            foreach( $list_month as $index => $month){
                                $monthsmall = strtolower($month);
                                $sub_total = $sub_total + $new_position_list[$key][strtolower($month)];
                                $total[$month] += $new_position_list[$key][strtolower($month)];
                    ?>
                                <td axis="<?php echo strtolower($month); ?>" style="vertical-align:middle; text-align:center;" class="text-center <?php echo ($index % 2 == 0 ? "even" : "odd"); ?> ">
                                <input type="text" style="width:20px" class="new_headcount_month_value" value="<?=$new_position_list[$key][strtolower($month)]; ?>" name="new_job_headcount_<?=strtolower($month)?>[<?=$new_position_list[$key]['annual_manpower_planning_position_id'];?>]" />
                                </td>
                    <?php
                            } 

                            $total['grand_total'] += $sub_total;

                            ?>

                            <td style="vertical-align:middle; text-align:center;" class="text-center even ">
                                <input type="text" style="width:20px" readonly="" class="new_headcount_month_total" value="<?=$new_position_list[$key]['total'];?>" name="new_job_headcount_total[<?=$new_position_list[$key]['annual_manpower_planning_position_id'];?>]" />
                            </td>
                            <td style="vertical-align:middle; text-align:center;" class="text-center even ">
                                <input type="text" style="width:60px" class="budget" value="<?=$new_position_list[$key]['budget'];?>" name="new_job_headcount_budget[<?=$new_position_list[$key]['annual_manpower_planning_position_id'];?>]" /></td>
                        </tr>
                        <tr>
                            <th style="border-top: none; text-align:left;">Remarks</th>
                            <td style="vertical-align:middle; text-align:left;" class="text-center even " colspan="14">
                            <textarea name="new_position_remarks[<?=$new_position_list[$key]['annual_manpower_planning_position_id'];?>]"><?php echo $new_position_list[$key]['remarks']; ?></textarea></td>
                        </tr>
                         </tbody>
                    <?php 
                    }

                }
                else{
                    ?>
        <tbody class="new_headcount_position_empty" >
            <tr><td colspan="17" style="text-align:center; font-weight:bold;">No new job added</td></tr>
        </tbody>

    <?php  } 

    ?>
    </table>
</div>

<?php
// if( $_POST['record_id'] == '-1' || ( $annual_status_id == 1 || $annual_status_id == 4 ) )
// {
?>

<div class="form-submit-btn align-right nopadding">
    <div class="icon-label-group">
        <div class="icon-label">
            <a rel="action-addnewheadcountposition" class="icon-16-add add_new_headcount_job" href="javascript:void(NULL)" onclick="">
                <span>Add New Job</span>
            </a>            
        </div>
    </div>
</div>

<?php
// }
?>

