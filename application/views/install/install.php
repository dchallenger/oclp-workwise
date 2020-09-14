<div class="container">
	<table class="zebra-striped">
		<tr>
			<td>Install script exists</td>
			<td>
				<span class="label <?php echo ($install_script_exists) ? 'success' : 'important' ?>">
					<?php echo ($install_script_exists) ? "TRUE" : "FALSE"?>
				</span>
			</td>
		</tr>				
	</table>
	<?php echo form_open('install/select_modules', 'method="post"');?>
		<input type="hidden" name="hash" value="<?php echo $hash;?>" />
		<input class="btn primary <?php echo ($errors) ? 'disabled' : ''?>" type="submit" value="Next" />
		<span class="label warning"><b>Caution:</b> Proceeding with installation will cause all previous data to be deleted.</span>
	<?php echo form_close();?>
</div>