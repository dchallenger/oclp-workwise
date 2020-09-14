<script type="text/javascript">
	$(document).ready(function () {
		$('#userform').validate();
	});
</script>

<style type="text/css">
	.input { overflow: hidden; }	
</style>

<div class="container">
	<h2>Create an admin account</h2>
	<hr />	
	<?php echo form_open('install/complete', 'id="userform"');?>
	<fieldset>
		<legend>Login Details</legend>
		<div class="clearfix">
			<label>Username</label>
			<div class="input"><input type="text" name="admin[username]" class="required"/></div>
		</div>
		<div class="clearfix">
			<label>Password</label>
			<div class="input"><input type="password" name="admin[password]" class="required"/></div>
		</div>				
	</fieldset>
	<fieldset>
		<legend>User Details</legend>
		<div class="clearfix">
			<label>First Name</label>
			<div class="input"><input type="text" name="admin[firstname]" class="required"/></div>
		</div>
		<div class="clearfix">
			<label>Last Name</label>
			<div class="input"><input type="text" name="admin[lastname]" class="required" /></div>
		</div>	
		<div class="clearfix">
			<label>Email</label>
			<div class="input"><input type="text" name="admin[email]" class="required email" /></div>
		</div>	
	</fieldset>	
		<input type="hidden" name="hash" value="<?php echo $hash;?>" />
		<input class="btn primary" type="submit" value="Complete Installation"/>		
	<?php echo form_close();?>
</div>