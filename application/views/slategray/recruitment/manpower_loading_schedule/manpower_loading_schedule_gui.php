<style type="text/css" media="screen">
    .text-left{ text-align: left !important}
    .rotate { height: 60px; vertical-align: bottom; cursor: pointer;  -moz-user-select: none; -webkit-user-select: none; }
    tbody tr th.module-name { cursor: pointer; -moz-user-select: none; -webkit-user-select: none; }
    .rotate div { -moz-transform: rotate(290deg); -webkit-transform: rotate(290deg); filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3.1); display: block; width: 16px; text-align: center; margin: 0 auto;}
</style>

<?php 

?>

<h3 class="form-head">Manpower Loading Schedule Setup</h3>

<div class="clear"></div>
<div class="spacer"></div>
<div id="module-access-container">
    <table class="default-table boxtype" style="width:100%" id="module-access">
            <colgroup width="15%"></colgroup>
            <thead>
                <tr class="">
                    <th style="text-align:left;" colspan="15">&nbsp;</th>
                </tr>
                <tr class="">
                    <th style="vertical-align:middle">Category</th><th class="action-name font-smaller even"><div>Remarks (Head Count)</div></th><th class="action-name font-smaller even"><div>Jan</div></th><th class="action-name font-smaller odd"><div>Feb</div></th><th class="action-name font-smaller even"><div>Mar</div></th><th class="action-name font-smaller odd"><div>Apr</div></th><th class="action-name font-smaller even"><div>May</div></th><th class="action-name font-smaller odd"><div>Jun</div></th><th class="action-name font-smaller even"><div>Jul</div></th><th class="action-name font-smaller odd"><div>Aug</div></th><th class="action-name font-smaller even"><div>Sep</div></th><th class="action-name font-smaller odd"><div>Oct</div></th><th class="action-name font-smaller even"><div>Nov</div></th><th class="action-name font-smaller odd"><div>Dec</div></th></tr>
            </thead>
            <tbody class="structure_list">
                <?php
                    $result = $this->db->get_where('user_position',array('deleted'=>0));
                    if ($result && $result->num_rows() > 0){
                        foreach ($result->result() as $row) {
                            $this->db->where('manpower_loading_schedule_id',$this->input->post('record_id'));
                            $this->db->where('position_id',$row->position_id);
                            $result = $this->db->get('manpower_loading_schedule_details');
                            $row_info = array();
                            if ($result && $result->num_rows() > 0){
                                $row_info = $result->row_array();
                            }
                            print '
                                <tr>
                                    <td>'.$row->position.'<input type="hidden" value="'.$row->position_id.'" name="position_id[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['remarks'].'" name="remarks[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['jan'].'" name="jan[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['feb'].'" name="feb[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['mar'].'" name="mar[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['apr'].'" name="apr[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['may'].'" name="may[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['jun'].'" name="jun[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['jul'].'" name="jul[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['aug'].'" name="aug[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['sep'].'" name="sep[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['oct'].'" name="oct[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['nov'].'" name="nov[]" /></td>
                                    <td><input type="text" style="width:100%" value="'.$row_info['dec'].'" name="dec[]" /></td>
                                </tr>';
                        }
                    }
                ?>
            </tbody>
        </table>
</div>
<br />