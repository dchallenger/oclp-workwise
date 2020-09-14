<style type="text/css">
table.interview{
    border-collapse:collapse;
}
table.interview th, table.interview td{
    border: 1px solid #D3D3D3;
}
table.interview th{
    background-color: #DEE3E1;
}
table.interview td,table.interview th {
    padding: 5px;
    width: 150px;
}    
</style>

<table class="interview">
    <thead>
        <tr>
            <th>Interviewer</th>
            <th>Date and Time</th>
            <th>Result</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
            if ($candidate_interviewer){
                foreach ($candidate_interviewer->result() as $row) {
        ?>
                    <tr id="<?php echo $row->candidate_interviewer_id ?>">
                        <td><?php echo $row->firstname ?>&nbsp;<?php echo $row->lastname ?></td>
                        <td><?php echo ($row->datetime != '0000-00-00 00:00:00' ? date('M d, Y',strtotime($row->datetime)) : '') ?></td>
                        <td><?php echo ($row->current_candidate_status ? $row->current_candidate_status : '') ?></td>
                        <td align="center">
                            <span class="icon-group">
                                <!-- <a module_link="recruitment/applicants" href="javascript:void(0)" tooltip="Edit" class="icon-button icon-16-info" original-title=""></a> -->
                                <a module_link="recruitment/applicants" href="javascript:void(0)" tooltip="Edit" interviewer-type="<?php echo $row->interviewer_type ?>" class="icon-button icon-16-edit" original-title=""></a>
                            </span>
                        </td>
                    </tr>
        <?php
                }
            }
        ?>
    </tbody>
</table>


<script type="text/javascript">
    function save_interviewer(candidate_interviewer_id){                             
        var result = $('input[name="result"]:radio:checked').val();
        var interviewer = $('#fullname').val();
        var strength = $('#strength').val();
        var areas_improvement = $('#areas_improvement').val();
        var recommendation = $('#recommendation').val();
        var fullname = $('#fullname').val();

        $.ajax({
            url: module.get_value('base_url') + 'recruitment/candidate_result/save_interviewer',
            data: 'candidate_interviewer_id=' + candidate_interviewer_id + '&candidate_id=' + module.get_value('record_id') + '&result=' + result + '&interviewer=' + interviewer + '&strength=' + strength + '&areas_improvement=' + areas_improvement + '&recommendation=' + recommendation + '&fullname=' + fullname,
            type: 'post',
            dataType: 'json',
            success: function(data) {
                $("tr[id='" + candidate_interviewer_id +"']").children('td:nth-child(3)').html(result);
                $.unblockUI();  
                Boxy.get($('#boxyhtml')).hide();
                $('#current_candidate_result').val(result);
                message_growl(data.msg_type, data.msg);
            }
        });                                 
    }
</script>