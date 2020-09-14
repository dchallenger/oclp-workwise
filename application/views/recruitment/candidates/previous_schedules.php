<?php if(isset($previous_schedules) && $previous_schedules):?>
<div>Previous schedules</div>
<hr />
<dl>   
<?php foreach ($previous_schedules as $schedule):?>
    <dd><?php echo  date('M j, Y g:i a', strtotime($schedule['interview_datetime']));?> scheduled via <?php echo $schedule['contacted_thru'];?></dd>
<?php endforeach;?>
</dl>
<?php endif;?>