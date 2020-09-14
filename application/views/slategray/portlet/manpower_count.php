<style type="text/css">
ul.table-header li{
	float: right;
	padding: 3px;
	width: 80px;
	text-align: center;
}
ul.table-body li{
	float: left;
	width: 80px;
	text-align: center;
}
.right {
	float: right;
	width: 80px;
	text-align: center;
	padding: 3px;
}
.left {
	float: left;
}
</style>

<?php
	$first_month_year = new DateTime(date('Y-01-01'));
	$date_diff = $first_month_year->diff(new DateTime());
	$count_month = $date_diff->m + 1;

	$today = date('Y-m-d');
	$prev_mo = date('Y-m-t',strtotime($today . ' - 1 month'));
	//$result_array = $this->portlet->get_cbe_non_cbe($prev_mo,$today);
	$result_array = $this->portlet->get_cbe_non_cbe_ytd_ave(date('Y-01-01'),$today,$count_month);
	$cbe_prev_mo = $result_array['cbe_prev_mo'];
	$cbe_cur_mo = $result_array['cbe_current_mo'];
	$cbe_ave = $result_array['cbe_ytd'];

	$non_cbe_prev_mo = $result_array['noncbe_prev_mo'];
	$non_cbe_cur_mo = $result_array['noncbe_current_mo'];
	$non_cbe_ave = $result_array['noncbe_ytd'];

	$support_prev_mo = $result_array['support_prev_mo'];
	$support_cur_mo = $result_array['support_current_mo'];
	$support_ave = $result_array['support_ytd'];
?>

<?php if ($this->user_access[$this->module_id]['post'] == 1) { ?>
	<ul class="table-header">
		<li>YTD Average</li>
		<li>Current MO</li>		
		<li>Previous MO</li>
	</ul>
	<br clear="all" />
	<ul>
		<li class="left">CBE</li>
		<li class="right"><?php echo $cbe_ave ?></li>
		<li class="right"><?php echo $cbe_cur_mo ?></li>
		<li class="right"><?php echo $cbe_prev_mo ?></li><br clear="all" />
		<h5 style="border-bottom:1px solid #EEEEEE;line-height:1px">&nbsp;</h5>
		<li class="left">Non-CBE</li>
		<li class="right"><?php echo $non_cbe_ave ?></li>
		<li class="right"><?php echo $non_cbe_cur_mo ?></li>
		<li class="right"><?php echo $non_cbe_prev_mo ?></li><br clear="all" />
		<h5 style="border-bottom:1px solid #EEEEEE;line-height:1px">&nbsp;</h5>	
		<li class="left"><b>TOTAL</b></li>
		<li class="right"><?php echo $cbe_ave + $non_cbe_ave ?></li>
		<li class="right"><?php echo $cbe_cur_mo + $non_cbe_cur_mo ?></li>
		<li class="right"><?php echo $cbe_prev_mo + $non_cbe_prev_mo ?></li><br clear="all" />
		<h5 style="border-bottom:1px solid #EEEEEE;line-height:1px">&nbsp;</h5>		
	</ul>

	<br />

	<h5>CBE COUNT</h5>

	<ul class="table-header">
		<li>YTD Average</li>
		<li>Current MO</li>		
		<li>Previous MO</li>
	</ul>
	<br clear="all" />
	<ul style="padding-left:10px">
		<li class="left">Support Group</li>
		<li class="right"><?php echo $support_ave ?></li>
		<li class="right"><?php echo $support_cur_mo ?></li>
		<li class="right"><?php echo $support_prev_mo ?></li><br clear="all" />
		<h5 style="border-bottom:1px solid #EEEEEE;line-height:1px">&nbsp;</h5>
		
		<?php
			$result = $this->db->get_where('user_company_division',array("deleted"=>0));
			if ($result && $result->num_rows() > 0){
				foreach ($result->result() as $row) {
					$result_array = $this->portlet->get_count_per_division_ytd_ave(date('Y-01-01'),$today,$count_month,$row->division_id);
					$count_prev_mo = $result_array['count_prev_mo'];
					$count_cur_mo = $result_array['count_current_mo'];
					$count_ave = $result_array['ytd'];

					print '<li class="left">'.acronym($row->division).'</li>
					<li class="right">'.$count_ave.'</li>
					<li class="right">'.$count_cur_mo.'</li>
					<li class="right">'.$count_prev_mo.'</li><br clear="all" />
					<h5 style="border-bottom:1px solid #EEEEEE;line-height:1px">&nbsp;</h5>';
				}
			}
		?>
	</ul>
<?php 
	} 
	else {
		if (count($result_array) > 0){ 
?>
			<ul class="table-header">
				<li>YTD Average</li>
				<li>Current MO</li>		
				<li>Previous MO</li>
			</ul>
			<br clear="all" />
			<ul>
				<li class="left">CBE</li>
				<li class="right"><?php echo $cbe_ave ?></li>
				<li class="right"><?php echo $cbe_cur_mo ?></li>
				<li class="right"><?php echo $cbe_prev_mo ?></li><br clear="all" />
				<h5 style="border-bottom:1px solid #EEEEEE;line-height:1px">&nbsp;</h5>
				<li class="left">Non-CBE</li>
				<li class="right"><?php echo $non_cbe_ave ?></li>
				<li class="right"><?php echo $non_cbe_cur_mo ?></li>
				<li class="right"><?php echo $non_cbe_prev_mo ?></li><br clear="all" />
				<h5 style="border-bottom:1px solid #EEEEEE;line-height:1px">&nbsp;</h5>	
				<li class="left"><b>TOTAL</b></li>
				<li class="right"><?php echo $cbe_ave + $non_cbe_ave ?></li>
				<li class="right"><?php echo $cbe_cur_mo + $non_cbe_cur_mo ?></li>
				<li class="right"><?php echo $cbe_prev_mo + $non_cbe_prev_mo ?></li><br clear="all" />
				<h5 style="border-bottom:1px solid #EEEEEE;line-height:1px">&nbsp;</h5>		
			</ul>
<?php 
		}
		else{
			print '<div>No Current Division.</div>';
		} 
	} 
?>