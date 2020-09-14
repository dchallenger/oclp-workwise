<p>&nbsp;</p>
<?php
$ab_flag = $rb_flag = FALSE;
?>

<table border="0" cellpadding="20" cellspacing="0" style="border:1px #333 solid; font-size:8pt;" width="100%">
	<tbody>
		<tr>
			<th style="border:1px #333 solid">Recommendation:</th>
			<th style="border:1px #333 solid">1st Interviewer</th>
			<th style="border:1px #333 solid">2nd Interviewer</th>
			<th style="border:1px #333 solid">3rd Interviewer</th>
		</tr>
		<?php foreach ($statuses as $status):?>
		<tr>
			<td style="border:1px #333 solid"><?=$status['candidate_status'];?></td>
			<td align="center" style="border:1px #333 solid">
				<input type="radio" name="recommended_1" value="<?=$status['candidate_status_id']?>" <?php echo ($recommended_1 == $status['candidate_status_id']) ? 'checked' : '' ?>
					<?=(!$this->is_admin) ? 'disabled' : '' ?>
				/>
			</td>
			<td align="center" style="border:1px #333 solid">
				<?php if ($this->userinfo['user_id'] != $requested_by ): $rb_flag = TRUE;?>
					<input type="hidden" name="recommended_2" value="<?=$recommended_2?>" />
				<?php endif ?>
				<input type="radio" name="recommended_2" value="<?=$status['candidate_status_id']?>" 
					<?php echo ($recommended_2 == $status['candidate_status_id']) ? 'checked' : '' ?>  
					<?php echo ($rb_flag) ? 'disabled' : ''?>
				       />
			</td>
			<td align="center" style="border:1px #333 solid">
				<?php if ($this->userinfo['user_id'] != $approved_by ): $ab_flag = TRUE;?>
					<input type="hidden" name="recommended_3" value="<?=$recommended_3?>" />
				<?php endif ?>				
				<input type="radio" name="recommended_3" value="<?=$status['candidate_status_id']?>" 
					<?php echo ($recommended_3 == $status['candidate_status_id']) ? 'checked' : '' ?> 
					<?php echo ($ab_flag) ? 'disabled' : ''?>
				       />
			</td>
		</tr>
		<?php endforeach;?>		
		<tr>
			<td style="border:1px #333 solid">Comment/s on your recommendation:</td>
			<td align="center" style="border:1px #333 solid">
				<textarea name="comment_1" <?=(!$this->is_admin) ? 'readonly' : '' ?>><?php echo $comment_1; ?></textarea>
			</td>
			<td align="center" style="border:1px #333 solid">
				<textarea name="comment_2" <?=($rb_flag) ? 'readonly' : ''?>><?php echo $comment_2; ?></textarea>
			</td>
			<td align="center" style="border:1px #333 solid">
				<textarea name="comment_3" <?=($ab_flag) ? 'readonly' : ''?>><?php echo $comment_3; ?></textarea>
			</td>
		</tr>
		<tr>
			<td style="border:1px #333 solid">
				<strong>Signature:</strong>
			</td>
			<td style="border:1px #333 solid"><?php echo $admin_name; ?></td>
			<td style="border:1px #333 solid"><?php echo $requested_by_name; ?></td>
			<td style="border:1px #333 solid"><?php echo $approved_by_name; ?></td>
		</tr>
	</tbody>
</table>